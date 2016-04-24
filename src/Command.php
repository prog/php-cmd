<?php

namespace com\peterbodnar\cmd;



/**
 * External command wrapper
 */
class Command {


	/** @var string ~ Path to executable. */
	protected $command;


	/**
	 * @param string $command ~ Path to executable.
	 */
	public function __construct($command) {
		$this->command = $command;
	}


	/**
	 * Execute command.
	 *
	 * @param string[] $arguments ~ Command line arguments.
	 * @param string|null $inputData ~ Input data.
	 * @return CommandResult
	 * @throws CommandException
	 */
	public function execute(array $arguments = [], $inputData = null) {
		$cmd = escapeshellcmd($this->command);
		foreach ($arguments as $name => $arg) {
			if (is_string($name)) {
				$arg = $name . "=" . $arg;
			}
			$cmd .= " " . escapeshellarg($arg);
		}

		$process = proc_open($cmd, [
			0 => ["pipe", "r"],
			1 => ["pipe", "w"],
			2 => ["pipe", "w"],
		], $pipes);
		if (!is_resource($process)) {
			throw new CommandException("Can not open process \"" . $cmd . "\"");
		}

		if (null !== $inputData) {
			fwrite($pipes[0], $inputData);
		}
		fclose($pipes[0]);
		$stdOut = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		$stdErr = stream_get_contents($pipes[2]);
		fclose($pipes[2]);
		$exitCode = proc_close($process);

		$result = new CommandResult();
		$result->stdOut = $stdOut;
		$result->stdErr = $stdErr;
		$result->exitCode = $exitCode;
		return $result;
	}

}



/**
 * Command Execution Result
 */
class CommandResult {


	/** @var string */
	public $stdOut;
	/** @var string */
	public $stdErr;
	/** @var int */
	public $exitCode;

}



/**
 * Command Exception
 */
class CommandException extends \Exception { }
