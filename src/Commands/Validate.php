<?php

namespace Bmatovu\Ussd\Commands;

use Illuminate\Console\Command;

class Validate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ussd:validate
                                {--f|file=menu.xml : Main menu file.}
                                {--s|schema=menu.xsd : XSD to validate against.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate ussd menus.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $xml = file_exists($this->option('file')) ? $this->option('file') : menus_path($this->option('file'));
        $xsd = file_exists($this->option('schema')) ? $this->option('schema') : menus_path($this->option('schema'));

        if (! file_exists($xsd)) {
            $xsd = __DIR__.'/../../menus/menu.xsd';
            $this->line("Using '{$xsd}'");
        }

        $errors = $this->validate($xml, $xsd);

        if (! $errors) {
            $this->info('OK');

            return;
        }

        foreach ($errors as $file => $messages) {
            $this->error('File: '.$file);
            $this->table(['Line', 'Element', 'Message'], $messages);
        }
    }

    /**
     * @see https://www.php.net/manual/en/class.simplexmlelement.php#107869
     */
    public function validate(string $xmlFile, string $xsdFile, int $flags = 0): array
    {
        libxml_use_internal_errors(true);

        $domDocument = new \DOMDocument();

        $domDocument->load($xmlFile);

        $errors = [];

        if ($domDocument->schemaValidate($xsdFile, $flags)) {
            return $errors;
        }

        foreach (libxml_get_errors() as $error) {
            // level, code, column, message, file, line
            $errors[$error->file][] = [
                $error->line,
                $this->getElement($error->message),
                $this->getMessage($error->message),
            ];
        }

        libxml_clear_errors();

        return $errors;
    }

    /**
     * Get element from message.
     */
    protected function getElement(string $message): ?string
    {
        $matches = [];

        preg_match("/'([-_\\w]+)'/", $message, $matches);

        return array_pop($matches);
    }

    /**
     * Get refined message.
     */
    protected function getMessage(string $message): string
    {
        $parts = explode(':', $message);

        return trim(array_pop($parts));
    }
}
