<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-VarDump
 */

namespace galastri\modules;

final class VarDump
{
    const ARRAY_KEY_HTML = '<span class="array-key">$1</span> => ';
    const ARRAY_KEY_REGEX = '/(\[(?:"|&quot;).*?(?:"|&quot;)]|\[\d+\])(?:=>|=&gt;)\n\s+/m';

    const ARRAY_TYPE_HTML = '<span class="array-type">$1</span>';
    const ARRAY_TYPE_REGEX = '/(array\(\d+\))/m';

    const FALSE_HTML = '<span class="false-value">$2</span> <span class="false-type">$1</span>';
    const FALSE_REGEX = '/(bool)(\(false\))/m';

    const NULL_HTML = '<span class="null">$1</span>';
    const NULL_REGEX = '/(NULL)/m';

    const NUMERIC_HTML = '<span class="numeric-value">$2</span> <span class="numeric-type">$1</span>';
    const NUMERIC_REGEX = '/(int|float)\((.*?)\)/m';

    const OBJECT_HTML = '<span class="object-header">$1</span> {';
    const OBJECT_REGEX = '/(object.*?)\s{/m';

    const STRING_HTML = '<span class="string-value">"$2"</span> <span class="string-type">$1</span>\n';
    const STRING_REGEX_1 = '/^(string\(\d+\)).*?\s(?:"|&quot;)(.*)(?:"|&quot;)$/m';
    const STRING_REGEX_2 = '/\s(?:=>|=&gt;)\s(string\(\d+\)).*?\s(?:"|&quot;)(.*?)(?:"|&quot;)\\\\n/m';

    const TRUE_HTML = '<span class="true-value">$2</span> <span class="true-type">$1</span>';
    const TRUE_REGEX = '/(bool)(\(true\))/m';

    const VISIBILITY_HTML = '<span class="object-key">[$1:<small>$2</small>]</span> => ';
    const VISIBILITY_REGEX = '/\[((?:"|&quot;).*?(?:"|&quot;)):.*?(private|protected)\](?:=>|=&gt;)\n\s+/m';

    const STYLESHEET_FILE = PROJECT_DIR.'/galastri/misc/vardump.css';

    private static int $backTraceLevel = 0;

    private function __construct() {}

    public static function print(mixed ...$values): void
    {
        $debug = debug_backtrace()[self::getBacktraceLevel(RESET_BACKTRACE_LEVEL)];

        foreach (self::getValues(...$values) as $varDumpValue) {
            $varDumpValue = preg_replace(self::OBJECT_REGEX, self::OBJECT_HTML, $varDumpValue);
            $varDumpValue = preg_replace(self::VISIBILITY_REGEX, self::VISIBILITY_HTML, $varDumpValue);
            $varDumpValue = preg_replace(self::ARRAY_KEY_REGEX, self::ARRAY_KEY_HTML, $varDumpValue);
            $varDumpValue = preg_replace(self::TRUE_REGEX, self::TRUE_HTML, $varDumpValue);
            $varDumpValue = preg_replace(self::FALSE_REGEX, self::FALSE_HTML, $varDumpValue);
            $varDumpValue = preg_replace(self::NULL_REGEX, self::NULL_HTML, $varDumpValue);
            $varDumpValue = preg_replace(self::ARRAY_TYPE_REGEX, self::ARRAY_TYPE_HTML, $varDumpValue);
            $varDumpValue = preg_replace(self::NUMERIC_REGEX, self::NUMERIC_HTML, $varDumpValue);

            $varDumpValue = str_replace("\n", "\\n", $varDumpValue);
            $varDumpValue = preg_replace(self::STRING_REGEX_1, self::STRING_HTML, $varDumpValue);
            $varDumpValue = preg_replace(self::STRING_REGEX_2, ' => '.self::STRING_HTML, $varDumpValue);
            $varDumpValue = str_replace("\\n", "\n", $varDumpValue);

            $varDump[] = $varDumpValue;
        }

        echo '<style>'.file_get_contents(self::STYLESHEET_FILE).'</style>';
        echo '<div class="dump">';
        echo '<h2><b>var_dump</b></h2>';
        echo '<div><b>ORIGIN: </b>'.str_replace('/', '<wbr>/', $debug['file']).'</div>';
        echo '<div><b>LINE:   </b>'.$debug['line'].'</div>';
        echo '<br>';
        echo '<div><b>VALUES: </b></div>';
        foreach ($varDump as $key => $varDumpResult) {
            echo '<div class="division">RESULT '.($key + 1).'</div>';
            echo '<pre>'.$varDumpResult.'</pre>';
        }
        echo '</div>';
    }
    
    public static function raw(mixed ...$values): void
    {
        echo '<pre>';
        echo implode("\n\n", self::getValues(...$values));
        echo '</pre>';
    }
    
    private static function getValues(mixed ...$values): array
    {
        ob_start();
        foreach ($values as $key => $value) {
            var_dump($value);
            $contents[] = htmlspecialchars(trim(ob_get_contents()));
            ob_clean();
        };

        return $contents;
    }
    
    public static function addBacktraceLevel(): void
    {
        self::$backTraceLevel++;
    }
    
    public static function getBacktraceLevel(bool $reset = KEEP_BACKTRACE_LEVEL): int
    {
        $level = self::$backTraceLevel;

        if ($reset) {
            self::$backTraceLevel = 0;
        }

        return $level;
    }
}
