<?php

// 1. Inteiros.
\VarDump::print(123);
\VarDump::print(-123);
\VarDump::print(0);
\VarDump::print(PHP_INT_MAX);
\VarDump::print(PHP_INT_MIN);

// 2. Decimais e valores especiais.
\VarDump::print(123.456);
\VarDump::print(-123.456);
\VarDump::print(0.0);
\VarDump::print(INF);
\VarDump::print(-INF);
\VarDump::print(NAN);

// 3. Booleanos e null.
\VarDump::print(true);
\VarDump::print(false);
\VarDump::print(null);

// 4. Strings simples e vazias.
\VarDump::print('Uma string normal');
\VarDump::print('');
\VarDump::print('123');
\VarDump::print('0');

// 5. Acentos e emojis.
\VarDump::print('André, ação, coração, maçã — 😀 🚀');

// 6. Aspas e barras.
\VarDump::print('Texto com "aspas duplas" e \'aspas simples\'');
\VarDump::print('C:\\pasta\\arquivo.php');

// 7. Barra e letra n literais, sem quebra de linha.
\VarDump::print('Primeira linha\nSegunda linha');

// 8. Quebras de linha reais.
\VarDump::print("Primeira linha\nSegunda linha\nTerceira linha");
\VarDump::print("Primeira linha\r\nSegunda linha");
\VarDump::print("\nTexto entre quebras\n");

// 9. Aspas imediatamente antes de uma quebra.
\VarDump::print("Texto com aspas no final\"\nOutra linha");

// 10. String que parece conter a estrutura de outro dump.
\VarDump::print(
    "Texto estranho\"\n  [0]=>\n  int(123)\n}\nstring(5) \"teste"
);

// 11. HTML: as tags devem aparecer escritas.
\VarDump::print('<b>Teste</b>');
\VarDump::print('<script>alert("Teste");</script>');
\VarDump::print('Caracteres: < > & " \'');
\VarDump::print('&amp; &lt; &gt;');

// 12. Strings com tabulação, byte nulo e UTF-8 inválido.
\VarDump::print("Coluna A\tColuna B");
\VarDump::print("Antes\0Depois");
\VarDump::print("UTF-8 inválido: \xC3\x28");

// 13. Arrays vazios, simples e associativos.
\VarDump::print([]);
\VarDump::print([10, 20, 30]);
\VarDump::print([
    'nome' => 'André',
    'ativo' => true,
    'idade' => 35,
    'saldo' => 123.45,
    'observacao' => null,
]);

// 14. Arrays dentro de arrays.
\VarDump::print([
    'primeiro' => [
        'segundo' => [
            'terceiro' => 'Chegamos ao último nível',
        ],
    ],
    'vazio' => [],
]);

// 15. Chaves e valores com caracteres estranhos.
\VarDump::print([
    '' => 'Chave vazia',
    -10 => 'Chave negativa',
    'chave "com aspas"' => "Valor com \"\nquebra de linha",
    "chave\ncom quebra" => 'Outro valor',
    '<b>chave</b>' => '<i>valor</i>',
]);

// 16. Objetos vazios e com propriedades.
\VarDump::print(new \stdClass());

\VarDump::print((object) [
    'nome' => 'André',
    'ativo' => true,
    'dados' => [
        'numero' => 123,
        'texto' => 'Teste',
    ],
]);

// 17. Propriedades públicas, protegidas e privadas.
class ObjetoTesteVarDump
{
    public string $publica = 'Valor público';
    protected string $protegida = 'Valor protegido';
    private string $privada = 'Valor privado';
}

\VarDump::print(new ObjetoTesteVarDump());

// 18. Propriedades ainda não inicializadas.
class NaoInicializadoTesteVarDump
{
    public int $numero;
    public string $texto;
    public bool $ativo;
    public array $lista;
    public ?string $textoOpcional;
    public int|string $numeroOuTexto;
    public \stdClass $objeto;
    public mixed $qualquerValor;
}

\VarDump::print(new NaoInicializadoTesteVarDump());

// 19. Referências para diferentes tipos de valores.
$numero = 123;
$texto = 'Texto por referência';
$booleano = true;
$nulo = null;
$array = ['interno' => 'Valor'];
$objeto = (object) ['nome' => 'André'];

\VarDump::print([
    'numero' => &$numero,
    'texto' => &$texto,
    'booleano' => &$booleano,
    'nulo' => &$nulo,
    'array' => &$array,
    'objeto' => &$objeto,
    'semReferencia' => 'Este não deve receber &',
]);

// 20. Mesmo valor compartilhado por referência.
$compartilhado = 10;

$referencias = [
    'primeiro' => &$compartilhado,
    'segundo' => &$compartilhado,
];

$compartilhado = 20;

\VarDump::print($referencias);

// 21. Recursão em array.
$arrayRecursivo = [];
$arrayRecursivo['eleMesmo'] = &$arrayRecursivo;

\VarDump::print($arrayRecursivo);

// 22. Recursão em objeto.
$objetoRecursivo = new \stdClass();
$objetoRecursivo->eleMesmo = $objetoRecursivo;

\VarDump::print($objetoRecursivo);

// 23. Mesmo objeto em posições diferentes, sem recursão.
$objetoCompartilhado = (object) ['valor' => 123];

\VarDump::print([
    'primeiro' => $objetoCompartilhado,
    'segundo' => $objetoCompartilhado,
]);

// 24. Recursos abertos, por referência e fechados.
$recurso = fopen('php://memory', 'w+');

\VarDump::print($recurso);
\VarDump::print([
    'recursoPorReferencia' => &$recurso,
]);

fclose($recurso);

\VarDump::print($recurso);

// 25. Enums simples e com valor associado.
enum EstadoTesteVarDump
{
    case Ativo;
    case Inativo;
}

enum StatusTesteVarDump: string
{
    case Pendente = 'pendente';
    case Concluido = 'concluido';
}

\VarDump::print(EstadoTesteVarDump::Ativo);
\VarDump::print(StatusTesteVarDump::Concluido);

// 26. Enum por referência.
$estado = EstadoTesteVarDump::Ativo;

\VarDump::print([
    'estado' => &$estado,
]);

// 27. Vários valores na mesma chamada.
\VarDump::print(
    123,
    'Texto',
    true,
    null,
    ['nome' => 'André'],
    (object) ['ativo' => true]
);

// 28. Chamadas consecutivas, para conferir o reinício da leitura.
\VarDump::print(['primeira' => ['interna' => 123]]);
\VarDump::print('Segunda chamada');
\VarDump::print(null);

// 29. UNKNOWN sem referência e com referência.
$resetPointer = new \ReflectionMethod(
    \VarDump::class,
    'resetDumpPointerPosition'
);

$applyFormats = new \ReflectionMethod(
    \VarDump::class,
    'applyFormats'
);

$resetPointer->invoke(null);
echo '<pre>'.$applyFormats->invoke(null, "UNKNOWN:0\n").'</pre>';

$resetPointer->invoke(null);
echo '<pre>'.$applyFormats->invoke(null, "&UNKNOWN:0\n").'</pre>';