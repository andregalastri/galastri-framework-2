<?php
namespace galastri\modules;

final class VarDump
{
    const STYLESHEET_FILE = PROJECT_DIR.'/galastri/misc/vardump.css';
    const MAX_FORMAT_DEPTH = 128;

    private static int $backTraceLevel = 0;
    private static int $dumpPointerPosition = 0;
    private static string $refMarker = '';
    private static int $currentDepth = 0;

    private function __construct() {}

    public static function print(mixed ...$valueList): void
    {
        $backtraceLevel = self::$backTraceLevel;
        self::resetBacktraceLevel();
        
        $debugBacktrace = debug_backtrace(
            DEBUG_BACKTRACE_IGNORE_ARGS, $backtraceLevel + 1
        );
        
        $debugFile = $debugBacktrace[$backtraceLevel]['file'] ?? 'Desconhecido';
        $debugLine = $debugBacktrace[$backtraceLevel]['line'] ?? 0;
        
        $varDumpOutput = self::getVarDumpOutput(...$valueList);

        $varDumpCss = file_get_contents(self::STYLESHEET_FILE);

        echo '<style>'.$varDumpCss.'</style>';

        $currentFile = self::escapeHtml($debugFile);
        $currentLine = self::escapeHtml($debugLine);

        echo '<div class="vardump">';
        echo '<h2><b>var_dump</b></h2>';
        echo '<div><b>ORIGIN: </b>'.str_replace('/', '<wbr>/', $currentFile).'</div>';
        echo '<div><b>LINE: </b>'.$currentLine.'</div>';
        echo '<br>';
        echo '<div><b>VALUES: </b></div>';

        foreach ($varDumpOutput as $key => $currentOutput) {
            
            self::$currentDepth = 0;
            self::resetDumpPointerPosition();
            
            $formattedDump = self::applyFormats($currentOutput);
            
            $isFormattedDumpNull = $formattedDump === null;
            $doesPointerDiff = self::$dumpPointerPosition !== strlen($currentOutput);

            // Se a leitura falhar ou ficar incompleta, exibir o dump original.
            if ($formattedDump === null || self::$dumpPointerPosition !== strlen($currentOutput)) {
                $formattedDump = self::escapeHtml($currentOutput);
            }

            echo '<div class="division">RESULT '.($key + 1).'</div>';
            echo '<pre>'.$formattedDump.'</pre>';
        }

        echo '</div>';
    }

    public static function raw(mixed ...$valueList): void
    {
        echo '<pre>';
        echo self::escapeHtml(implode("\n", self::getVarDumpOutput(...$valueList)));
        echo '</pre>';
    }

    private static function getVarDumpOutput(mixed ...$valueList): array
    {
        $varDumpOutput = [];
        ob_start();

        try {
            foreach ($valueList as $value) {
                var_dump($value);
                $varDumpOutput[] = ob_get_contents();
                ob_clean();
            }
        } finally {
            ob_end_clean();
        }

        return $varDumpOutput;
    }
    
    private static function getMatches(string $regex, string $currentOutput): array | bool
    {
        $hasMatch = preg_match($regex, $currentOutput, $matches, 0, self::$dumpPointerPosition) === 1;
        
        if ($hasMatch) {
            return $matches;
        }
        
        return false;
    }
    
    private static function applyFormats(string $dump): ?string
    {
        if (self::$currentDepth > self::MAX_FORMAT_DEPTH) {
            return null;
        }

        self::$refMarker = '';

        if (substr($dump, self::$dumpPointerPosition, 1) === '&') {
            self::$refMarker = '&amp;';
            self::moveDumpPointerForward(1);
        }

        $regex = [
            'string'        => '/\G(string\((\d+)\)) "/',
            'array'         => '/\G(array\((\d+)\)) \{\n/',
            'object'        => '/\G((?:lazy (?:ghost|proxy) )?object\([^\r\n]*\)#\d+ \((\d+)\)) \{\n/',
            'numeric'       => '/\G(int|float)\(([^()\r\n]*)\)\n/',
            'boolean'       => '/\Gbool\((true|false)\)\n/',
            'recursion'     => '/\G\*RECURSION\*\n/',
            'uninitialized' => '/\Guninitialized\(([^\r\n]*)\)\n/',
            'resource'      => '/\Gresource\((\d+)\) of type \(([^\r\n]*)\)\n/',
            'enum'          => '/\Genum\(([^\r\n]*)\)\n/',
            'unknown'       => '/\GUNKNOWN:0\n/',
        ];

        if ($matches = self::getMatches($regex['string'], $dump)) {
            return self::stringFormatting($dump, $matches);
        }
        
        if ($matches = self::getMatches($regex['array'], $dump)) {
            return self::arrayFormatting($dump, $matches);
        }

        if ($matches = self::getMatches($regex['object'], $dump)) {
            return self::objectFormatting($dump, $matches);
        }
        
        if ($matches = self::getMatches($regex['numeric'], $dump)) {
            return self::numericFormatting($matches);
        }

        if ($matches = self::getMatches($regex['boolean'], $dump)) {
            return self::booleanFormatting($matches);
        }

        if ($matches = self::getMatches($regex['recursion'], $dump)) {
            return self::recursionFormatting($matches);
        }

        if ($matches = self::getMatches($regex['uninitialized'], $dump)) {
            return self::uninitializedFormatting($matches);
        }

        if ($matches = self::getMatches($regex['resource'], $dump)) {
            return self::resourceFormatting($matches);
        }

        if ($matches = self::getMatches($regex['enum'], $dump)) {
            return self::enumFormatting($matches);
        }

        if ($matches = self::getMatches($regex['unknown'], $dump)) {
            return self::unknownFormatting($matches);
        }
        
        $nullMatches = substr($dump, self::$dumpPointerPosition, 5) === "NULL\n";

        if ($nullMatches) {
            return self::nullFormatting();
        }
        
        return null;
    }
    
    private static function stringFormatting(string $currentoutput, array $matches): ?string
    {
        $matchLength = strlen($matches[0]);
        $outputlength = $matches[2];

        self::moveDumpPointerForward($matchLength);

        if ($outputlength > strlen($currentoutput) - self::$dumpPointerPosition - 2) {
            return null;
        }

        $value = substr($currentoutput, self::$dumpPointerPosition, $outputlength);

        self::moveDumpPointerForward($outputlength);

        if (substr($currentoutput, self::$dumpPointerPosition, 2) !== "\"\n") {
            return null;
        }

        self::moveDumpPointerForward(2);

        return self::$refMarker.self::applyHtml('string-value', '"'.$value.'"').' '.self::applyHtml('string-type', $matches[1])."\n";
    }

    private static function arrayFormatting(string $currentoutput, array $matches): ?string
    {
        $matchLength = strlen($matches[0]);
        $outputlength = $matches[2];

        self::moveDumpPointerForward($matchLength);

        $html = self::readContainer($currentoutput, $matches[1], $outputlength, false);

        if ($html === null) {
            return null;
        }

        return self::$refMarker.$html;
    }

    private static function objectFormatting(string $currentoutput, array $matches): ?string
    {
        $matchLength = strlen($matches[0]);
        $outputlength = $matches[2];

        self::moveDumpPointerForward($matchLength);

        $html = self::readContainer($currentoutput, $matches[1], $outputlength, true);

        if ($html === null) {
            return null;
        }

        return self::$refMarker.$html;
    }

    private static function numericFormatting(array $matches): ?string
    {
        $matchLength = strlen($matches[0]);
        $outputlength = $matches[2];

        self::moveDumpPointerForward($matchLength);

        return self::$refMarker.self::applyHtml('numeric-value', $outputlength).' '.self::applyHtml('numeric-type', $matches[1])."\n";
    }

    private static function booleanFormatting(array $matches): ?string
    {
        $matchLength = strlen($matches[0]);
        $boolType = $matches[1];

        self::moveDumpPointerForward($matchLength);


        return self::$refMarker.self::applyHtml($boolType.'-value', '('.$boolType.')').' '.self::applyHtml($boolType.'-type', 'bool')."\n";
    }

    private static function recursionFormatting(array $matches): ?string
    {
        $matchLength = strlen($matches[0]);

        self::moveDumpPointerForward($matchLength);

        return self::$refMarker.self::applyHtml('recursion', '*RECURSION*')."\n";
    }

    private static function uninitializedFormatting(array $matches): ?string
    {
        $matchLength = strlen($matches[0]);

        self::moveDumpPointerForward($matchLength);

        return self::$refMarker.self::applyHtml('uninitialized', 'uninitialized').' '.self::formatType($matches[1])."\n";
    }

    private static function resourceFormatting(array $matches): ?string
    {
        $matchLength = strlen($matches[0]);

        self::moveDumpPointerForward($matchLength);

        return self::$refMarker.self::applyHtml('resource-value', $matches[1]).' '.self::applyHtml('resource-type', 'resource').' '.self::applyHtml('resource-kind', '('.$matches[2].')')."\n";
    }

    private static function enumFormatting(array $matches): ?string
    {
        $matchLength = strlen($matches[0]);

        self::moveDumpPointerForward($matchLength);

        return self::$refMarker.self::applyHtml('enum-value', $matches[1]).' '.self::applyHtml('enum-type', 'enum')."\n";
    }

    private static function unknownFormatting(array $matches): ?string
    {
        $matchLength = strlen($matches[0]);

        self::moveDumpPointerForward($matchLength);

        return self::$refMarker.self::applyHtml('unknown', 'UNKNOWN:0')."\n";
    }

    private static function nullFormatting(): ?string
    {
        self::moveDumpPointerForward(5);

        return self::$refMarker.self::applyHtml('null', 'NULL')."\n";
    }

    
    
    
    
    
    private static function readContainer(string $dump, string $header, int $count, bool $isObject): ?string {
        $indent = str_repeat(' ', self::$currentDepth * 2);
        $childIndent = $indent.'  ';

        $headerClass = $isObject ? 'object-header' : 'array-type';

        $html = self::applyHtml($headerClass, $header)." {\n";

        $keyRegex = '/\G'.$childIndent.'(\[(?:-?\d+|"[\s\S]*?"(?::(?:private|protected))?)\])=>\n'.$childIndent.'/';

        for ($index = 0; $isObject || $index < $count; $index++) {
            // Objetos podem exibir propriedades não incluídas na contagem do cabeçalho.
            if ($isObject && substr($dump, self::$dumpPointerPosition, strlen($indent) + 2) === ($indent."}\n")) {
                break;
            }
            
            if (preg_match($keyRegex, $dump, $matches, 0, self::$dumpPointerPosition) !== 1) {
                return null;
            }
            
            self::moveDumpPointerForward(strlen($matches[0]));
            
            $key = $matches[1];
            
            self::$currentDepth++;

            try {
                $value = self::applyFormats($dump);
            } finally {
                self::$currentDepth--;
            }

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
            $html .= $childIndent.self::applyHtml($keyClass, $key).' =&gt; '.$value;
        }

        $closing = $indent."}\n";

        if (substr($dump, self::$dumpPointerPosition, strlen($closing)) !== $closing) {
            return null;
        }

        self::moveDumpPointerForward(strlen($closing));

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
        $parts = preg_split('/([?&|()])/', $type, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if ($parts === false) {
            return self::escapeHtml($type);
        }

        $html = [];

        foreach ($parts as $part) {
            if (in_array($part, ['?', '&', '|', '(', ')'], true)) {
                $html[] = self::escapeHtml($part);
            } else {
                // Nomes de classes e interfaces recebem o estilo de objeto.
                $class = $classes[$part] ?? 'object-header';
                $html[] = self::applyHtml($class, $part);
            }
        }

        return implode('', $html);
    }

    private static function applyHtml(string $class, string $value): string
    {
        return '<span class="'.$class.'">'.self::escapeHtml($value).'</span>';
    }

    private static function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function addBacktraceLevel(): void
    {
        self::$backTraceLevel++;
    }
    
    private static function moveDumpPointerForward(int $moves): void
    {
        self::$dumpPointerPosition += $moves;
    }

    private static function resetDumpPointerPosition(): void
    {
        self::$dumpPointerPosition = 0;
    }

    public static function resetBacktraceLevel(): void
    {
        self::$backTraceLevel = 0;
    }
}
