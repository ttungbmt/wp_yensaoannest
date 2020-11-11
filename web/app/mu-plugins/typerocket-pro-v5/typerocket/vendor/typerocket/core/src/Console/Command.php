<?php
namespace TypeRocket\Console;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class Command
 *
 * @link http://symfony.com/doc/current/components/console.html
 *
 * @package TypeRocket\Console
 */
class Command extends SymfonyCommand
{
    const REQUIRED = InputArgument::REQUIRED;
    const OPTIONAL = InputArgument::OPTIONAL;
    const IS_ARRAY = InputArgument::IS_ARRAY;

    /** @var InputInterface $input */
    protected $input;

    /** @var OutputInterface $output */
    protected $output;

    protected $command = [
        'name',
        'description',
        'help',
    ];

    protected $printedError = false;
    protected $success;

    /**
     * Configure
     */
    protected function configure()
    {

        $signature = explode(' ', $this->command[0], 2);
        $name = array_shift($signature);

        $this->setName($name)
             ->setDescription($this->command[1])
             ->setHelp($this->command[2]);

        if($signature) {
            // Match Laravel style: name:command {?user*} {?name=kevin} {?--option=some value}
            preg_match_all('/(\{.+\})/mU', $signature[0], $matches, PREG_SET_ORDER, 0);
            foreach ($matches as [$arg, $other]) {
                $arg = substr($arg, 1, -1);
                $mode = static::REQUIRED;
                $shortcut = null;
                $is_option = false;

                [$arg, $default] = array_pad(explode('=', $arg, 2), 2, null);

                if(trim($arg, '?') !== $arg) {
                    $mode = static::OPTIONAL;
                    $arg = trim($arg, '?');
                }

                if($arg[0] == '-') {
                    $arg = ltrim($arg, '-');
                    [$shortcut, $arg] = array_pad(explode('|', $arg, 2), 2, null);

                    if(is_null($arg)) {
                        $arg = $shortcut;
                        $shortcut = $arg[0];
                    }

                    $is_option = true;
                }

                if(trim($arg, '*') !== $arg || ($default == '*' && $is_option)) {
                    $mode = $mode + static::IS_ARRAY;
                    $arg = trim($arg, '*');
                    $default = null;
                }

                if($is_option) {
                    $bitWiseDiff = InputOption::VALUE_REQUIRED / static::REQUIRED;
                    $this->addOption($arg, $shortcut, $mode * $bitWiseDiff, '', $default);
                } else {
                    $this->addArgument($arg, $mode, '', $default);
                }
            }
        }

        $this->config();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->exec();

        return $this->success ?? Command::SUCCESS;
    }

    /**
     * Add your configuration
     */
    protected function config()
    {
    }

    /**
     * Add your execution
     */
    protected function exec()
    {
    }

    /**
     * Output error line
     *
     * @param string $content
     */
    protected function error( $content )
    {
        $this->printedError = true;
        $this->output->writeln('<fg=red>'.$content.'</>');
    }

    /**
     * Output success line
     *
     * @param string $content
     */
    protected function success($content)
    {
        $this->output->writeln('<fg=green>'.$content.'</>');
    }

    /**
     * Output warning line
     *
     * @param string $content
     */
    protected function warning($content)
    {
        $this->output->writeln('<fg=yellow>'.$content.'</>');
    }

    /**
     * Output info line
     *
     * @param string $content
     */
    protected function info($content)
    {
        $this->output->writeln('<fg=cyan>'.$content.'</>');
    }

    /**
     * Output line
     *
     * @param string $content
     */
    protected function line($content)
    {
        $this->output->writeln($content);
    }

    /**
     * Confirm
     *
     * https://symfony.com/doc/3.4/components/console/helpers/questionhelper.html
     *
     * @param string|null $question        The question to ask to the user
     * @param bool   $default         The default answer to return, true or false
     * @param string $trueAnswerRegex A regex to match the "yes" answer
     */
    protected function confirm($question = null, $default = false, $trueAnswerRegex = '/^y/i') {
        $question = new ConfirmationQuestion(($question ?? 'Continue with this action? (y|n)') . ' ', $default, $trueAnswerRegex);

        if (!$this->getHelper('question')->ask($this->input, $this->output, $question)) {
            die();
        }
    }

    /**
     * Get Argument
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function getArgument( $name ) {
        return $this->input->getArgument($name);
    }

    /**
     * @param string $name
     * @param array $args
     *
     * @throws \Exception
     */
    protected function runCommand($name, array $args = []) {
        $command = $this->getApplication()->find($name);
        $input = new ArrayInput( $args );
        $command->run($input, $this->output);
    }

    /**
     * Get Option
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function getOption( $name ) {
        return $this->input->getOption($name);
    }

    /**
     * Get Class Arg
     *
     * @param $arg
     *
     * @return mixed|string|string[]|null
     */
    public function getClassArgument($arg) {
        $arg = $this->getArgument($arg);
        $arg = str_replace("/",'\\', $arg);
        $arg = preg_replace('/(\\\\+)/m','\\', $arg);

        return $arg;
    }

}