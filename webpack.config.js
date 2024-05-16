const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const { resolve } = require("path");
const { spawn } = require("child_process");
const chalk = require("chalk");

let rsyncError = false;

module.exports = {
	...defaultConfig,
	plugins: [
		...defaultConfig.plugins,
		/**
		 * During watch mode we want to sync the build directory to the server.
		 * This relies on the user setting the RSYNC_DESTINATION environment variable.
		 * If it doesn't exist then we don't do anything.
		 */
		process.env.RSYNC_DESTINATION && rsyncError === false
			? {
					apply: (compiler) => {
						compiler.hooks.done.tap("CustomWatchClosePlugin", (stats) => {
							if (
								!stats.hasErrors() &&
								!stats.hasWarnings() &&
								stats.compilation.compiler.watchMode
							) {
								console.log("!!! --- UPLOADING TO SERVER --- !!!");

								const rsync = spawn("rsync", [
									"-avz",
									resolve(".", "build"),
									process.env.RSYNC_DESTINATION,
								]);

								/**
								 * Handle rsync output
								 *
								 * strip everything except the sent bytes
								 */
								rsync.stdout.on("data", (data) => {
									if (data.toString().includes("sent")) {
										console.log(data.toString());
									}
								});

								/**
								 * Handle rsync errors
								 *
								 * If we have any errors we set the rsyncError flag to true
								 */
								rsync.stderr.on("data", (data) => {
									console.error(chalk.red(`rsync stderr: ${data}`));

									rsyncError = true;
								});

								/**
								 * Handle rsync completion
								 */
								rsync.on("close", (code) => {
									if (code === 0) {
										console.log(chalk.green("!!! --- UPLOAD SUCCESS --- !!!"));
									} else {
										console.log(
											chalk.red(`rsync process exited with code ${code}`),
										);
									}
								});
							}
						});
					},
			  }
			: undefined,
	],
};
