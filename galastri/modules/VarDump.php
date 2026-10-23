<?php
namespace galastri\modules;

final class VarDump
{
    const STYLESHEET_FILE = PROJECT_DIR.'/galastri/misc/vardump.css';
    const MAX_FORMAT_DEPTH = 128;

    private static int $backTraceLevel = 0;

    private function __construct() {}

    public static function print(mixed ...$values): void
    {
        $level = self::getBacktraceLevel(RESET_BACKTRACE_LEVEL);
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $level + 1);
        $debug = $trace[$level] ?? [];
        $contents = self::getValues(...$values);

        if (is_readable(self::STYLESHEET_FILE)) {
            $stylesheet = file_get_contents(self::STYLESHEET_FILE);

            if ($stylesheet !== false) {
                echo '<style>'.$stylesheet.'</style>';
            }
        }

        $file = self::escape($debug['file'] ?? 'Desconhecido');
        $line = self::escape((string) ($debug['line'] ?? 0));

        echo '<div class="dump">';
        echo '<h2><b>var_dump</b></h2>';
        echo '<div><b>ORIGIN: </b>'.str_replace('/', '<wbr>/', $file).'</div>';
        echo '<div><b>LINE: </b>'.$line.'</div>';
        echo '<br>';
        echo '<div><b>VALUES: </b></div>';

        foreach ($contents as $key => $dump) {
            echo '<div class="division">RESULT '.($key + 1).'</div>';
            echo '<pre>'.self::format($dump).'</pre>';
        }

        echo '</div>';
    }

    public static function raw(mixed ...$values): void
    {
        echo '<pre>';
        echo self::escape(implode("\n", self::getValues(...$values)));
        echo '</pre>';
    }

    private static function getValues(mixed ...$values): array
    {
        $contents = [];
        ob_start();

        try {
            foreach ($values as $value) {
                \var_dump($value);
                $contents[] = ob_get_contents();
                ob_clean();
            }
        } finally {
            ob_end_clean();
        }

        return $contents;
    }

    private static function format(string $dump): string
    {
        $position = 0;
        $html = self::readValue($dump, $position, 0);

        // Não aproveitar uma interpretação incompleta da saída.
        if ($html === null || $position !== strlen($dump)) {
            return self::escape($dump);
        }

        return $html;
    }

    private static function readValue(string $dump, int &$position, int $depth ): ?string
    {
        if ($depth > self::MAX_FORMAT_DEPTH) {
            return null;
        }

        $reference = '';

        if (substr($dump, $position, 1) === '&') {
            $reference = '&amp;';
            $position++;
        }

        if (preg_match('/\G(string\((\d+)\)) "/', $dump, $matches, 0, $position) === 1) {
            $position += strlen($matches[0]);
            $length = (int) $matches[2];

            // string(N) informa bytes. Não converter para HTML antes da leitura.
            if ($length > strlen($dump) - $position - 2) {
                return null;
            }

            $value = substr($dump, $position, $length);
            $position += $length;

            if (substr($dump, $position, 2) !== "\"\n") {
                return null;
            }

            $position += 2;

            return $reference.self::span('string-value', '"'.$value.'"')
                .' '.self::span('string-type', $matches[1])."\n";
        }

        if (preg_match('/\G(array\((\d+)\)) \{\n/', $dump, $matches, 0, $position) === 1) {
            $position += strlen($matches[0]);

            $html = self::readContainer(
                $dump,
                $position,
                $depth,
                $matches[1],
                (int) $matches[2],
                false
            );

            if ($html === null) {
                return null;
            }

            return $reference.$html;
        }

        $objectRegex = '/\G((?:lazy (?:ghost|proxy) )?object\([^\r\n]*\)#\d+ \((\d+)\)) \{\n/';

        if (preg_match($objectRegex, $dump, $matches, 0, $position) === 1) {
            $position += strlen($matches[0]);

            $html = self::readContainer(
                $dump,
                $position,
                $depth,
                $matches[1],
                (int) $matches[2],
                true
            );

            if ($html === null) {
                return null;
            }

            return $reference.$html;
        }

        if (preg_match('/\G(int|float)\(([^()\r\n]*)\)\n/', $dump, $matches, 0, $position) === 1) {
            $position += strlen($matches[0]);

            return $reference.self::span('numeric-value', $matches[2])
                .' '.self::span('numeric-type', $matches[1])."\n";
        }

        if (preg_match('/\Gbool\((true|false)\)\n/', $dump, $matches, 0, $position) === 1) {
            $position += strlen($matches[0]);
            $type = $matches[1];

            return $reference.self::span($type.'-value', '('.$type.')')
                .' '.self::span($type.'-type', 'bool')."\n";
        }

        if (substr($dump, $position, 5) === "NULL\n") {
            $position += 5;

            return $reference.self::span('null', 'NULL')."\n";
        }
        
        if (preg_match('/\G\*RECURSION\*\n/', $dump, $matches, 0, $position) === 1) {
            $position += strlen($matches[0]);

            return $reference.self::span('recursion', '*RECURSION*')."\n";
        }

        if (preg_match('/\Guninitialized\(([^\r\n]*)\)\n/', $dump, $matches, 0, $position) === 1) {
            $position += strlen($matches[0]);

            return $reference.self::span('uninitialized', 'uninitialized')
                .' '.self::formatType($matches[1])."\n";
        }
        
        if (preg_match('/\Gresource\((\d+)\) of type \(([^\r\n]*)\)\n/', $dump, $matches, 0, $position) === 1) {
            $position += strlen($matches[0]);

            return $reference.self::span('resource-value', $matches[1])
                .' '.self::span('resource-type', 'resource')
                .' '.self::span('resource-kind', '('.$matches[2].')')."\n";
        }

        if (preg_match('/\Genum\(([^\r\n]*)\)\n/', $dump, $matches, 0, $position) === 1) {
            $position += strlen($matches[0]);

            return $reference.self::span('enum-value', $matches[1])
                .' '.self::span('enum-type', 'enum')."\n";
        }

        if (preg_match('/\GUNKNOWN:0\n/', $dump, $matches, 0, $position) === 1) {
            $position += strlen($matches[0]);

            return $reference.self::span('unknown', 'UNKNOWN:0')."\n";
        }

        if (preg_match($otherRegex, $dump, $matches, 0, $position) === 1) {
            $position += strlen($matches[0]);

            return $reference.self::escape($matches[0]);
        }

        return null;
    }

    private static function readContainer(
        string $dump,
        int &$position,
        int $depth,
        string $header,
        int $count,
        bool $isObject
    ): ?string {
        $indent = str_repeat(' ', $depth * 2);
        $childIndent = $indent.'  ';

        if ($isObject) {
            $headerClass = 'object-header';
        } else {
            $headerClass = 'array-type';
        }

        $html = self::span($headerClass, $header)." {\n";

        $keyRegex = '/\G'.$childIndent.'(\[(?:-?\d+|"[\s\S]*?"(?::(?:private|protected))?)\])=>\n'.$childIndent.'/';

        for ($index = 0; $isObject || $index < $count; $index++) {
            // Objetos podem exibir propriedades não incluídas na contagem do cabeçalho.
            if ($isObject && substr($dump, $position, strlen($indent) + 2) === ($indent."}\n")) {
                break;
            }
            
            if (preg_match($keyRegex, $dump, $matches, 0, $position) !== 1) {
                return null;
            }

            $position += strlen($matches[0]);
            $key = $matches[1];
            $value = self::readValue($dump, $position, $depth + 1);

            if ($value === null) {
                return null;
            }

            // Preservar a chave inteira, incluindo a classe declaradora.
            $keyClass = 'array-key';

            if ($isObject) {
                $keyClass = 'object-key';

                if (preg_match('/:(private|protected)\]$/', $key, $visibility) === 1) {
                    $keyClass .= ' '.$visibility[1];
                }
            }

            // Preservar a chave inteira, incluindo a classe declaradora e a visibilidade.
            $html .= $childIndent.self::span($keyClass, $key).' =&gt; '.$value;
        }

        $closing = $indent."}\n";

        if (substr($dump, $position, strlen($closing)) !== $closing) {
            return null;
        }

        $position += strlen($closing);

        return $html.$closing;
    }
    
    private static function formatType(string $type): string
    {
        $classes = [
            'int' => 'numeric-type',
            'float' => 'numeric-type',
            'string' => 'string-type',
            'bool' => 'boolean-type',
            'true' => 'true-type',
            'false' => 'false-type',
            'null' => 'null-type',
            'array' => 'array-type',
            'object' => 'object-header',
            'mixed' => 'generic-type',
            'iterable' => 'generic-type'
        ];

        // Separar os tipos, preservando os operadores e os parênteses.
        $parts = preg_split(
            '/([?&|()])/',
            $type,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        if ($parts === false) {
            return self::escape($type);
        }

        $html = [];

        foreach ($parts as $part) {
            if (in_array($part, ['?', '&', '|', '(', ')'], true)) {
                $html[] = self::escape($part);
            } else {
                // Nomes de classes e interfaces recebem o estilo de objeto.
                $class = $classes[$part] ?? 'object-header';
                $html[] = self::span($class, $part);
            }
        }

        return implode('', $html);
    }

    private static function span(string $class, string $value): string
    {
        return '<span class="'.$class.'">'.self::escape($value).'</span>';
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
