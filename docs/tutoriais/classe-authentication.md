```php
/**
 * -----------------------------------------------------------------------------
 * 📖
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                      TUTORIAL: CLASSE Authentication                      ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 *
 * Neste tutorial, vamos acompanhar casos de uso práticos da classe
 * `Authentication` dentro de uma aplicação do Galastri Framework.
 *
 * Em vez de olhar cada método isolado, vamos montar um cenário completo e
 * contínuo: uma aplicação com área pública, tela de login, painel
 * administrativo protegido, área do cliente separada e uma rota de API.
 */



/**
 * 🟢 CENÁRIO E CONFIGURAÇÃO DAS ROTAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Antes de chamar qualquer método de `Authentication` nos controllers,
 * precisamos configurar no arquivo `/app/config/routes.php` quais rotas são
 * públicas e quais exigem autenticação.
 *
 * Lembra das configurações `authTag`, `authFailRedirectTo` e `authFailMessage`?
 * É com elas que dizemos ao framework quando chamar
 * `Authentication::validate()` automaticamente e o que fazer se a autenticação
 * não for válida.
 *
 * 🖥️ EXEMPLO DE CONFIGURAÇÃO EM /app/config/routes.php
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                                     │
 * │                                                                           │
 * │ return [                                                                  │
 * │     '/' => [                                                              │
 * │         'output' => 'view',                                               │
 * │                                                                           │
 * │         '@main' => [],                                                    │
 * │                                                                           │
 * │         '/login' => [                                                     │
 * │             '@main' => [                                                  │
 * │                 'onHttpMethod' => [                                       │
 * │                     'POST' => [                                           │
 * │                         'method' => 'autenticar',                         │
 * │                     ],                                                    │
 * │                 ],                                                        │
 * │             ],                                                            │
 * │         ],                                                                │
 * │                                                                           │
 * │         '/painel-admin' => [                                              │
 * │             'authTag' => 'acesso-admin',                                  │
 * │             'authFailRedirectTo' => '/login',                             │
 * │                                                                           │
 * │             '@main' => [],                                                │
 * │             '@perfil' => [                                                │
 * │                 'onHttpMethod' => [                                       │
 * │                     'POST' => [                                           │
 * │                         'method' => 'atualizarPerfil',                    │
 * │                     ],                                                    │
 * │                 ],                                                        │
 * │             ],                                                            │
 * │             '@renovar' => [],                                             │
 * │             '@sair' => [],                                                │
 * │         ],                                                                │
 * │                                                                           │
 * │         '/area-cliente' => [                                              │
 * │             'authTag' => 'acesso-cliente',                                │
 * │             'authFailRedirectTo' => '/login',                             │
 * │                                                                           │
 * │             '@main' => [],                                                │
 * │         ],                                                                │
 * │                                                                           │
 * │         '/api' => [                                                       │
 * │             'output' => 'json',                                           │
 * │             'authTag' => 'acesso-admin',                                  │
 * │             'authFailMessage' => 'Autenticação necessária.',              │
 * │                                                                           │
 * │             '@dados' => [],                                               │
 * │         ],                                                                │
 * │     ],                                                                    │
 * │ ];                                                                        │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Com essas rotas definidas:
 *
 * - As rotas `/` e `/login` ficam abertas pra qualquer visitante. Em `/login`,
 *   uma requisição `GET` executa `main()`, exibindo o formulário. Já uma
 *   requisição `POST` executa diretamente `autenticar()`, conforme configurado
 *   em `onHttpMethod`.
 *
 * - Tudo dentro de `/painel-admin` exige uma autenticação válida com a tag
 *   `'acesso-admin'`. Se o usuário não estiver autenticado, o framework o
 *   redireciona para `/login`.
 *
 * - A rota `/area-cliente` usa uma tag diferente (`'acesso-cliente'`),
 *   mostrando que a mesma sessão pode controlar acessos independentes.
 *
 * - A rota `/api/dados` usa saída `json`. Como ela também exige
 *   `'acesso-admin'` e o framework não redireciona saídas JSON, configuramos
 *   `authFailMessage` pra devolver uma mensagem de erro quando a autenticação
 *   falhar.
 */



/**
 * 🟢 CASO 1: LOGIN E REGISTRO DA AUTENTICAÇÃO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Agora que as rotas estão configuradas, vamos implementar o controller de
 * login em `/app/controllers/Login.php`.
 *
 * Ele cuida de dois momentos:
 *
 * - Ao abrir `/login` (`GET`), conferimos manualmente se o usuário já está
 *   autenticado. Se estiver, já o mandamos direto para `/painel-admin`.
 *
 * - Ao enviar o formulário (`POST`), validamos os dados informados pela
 *   aplicação e, se estiverem corretos, chamamos `Authentication::register()`
 *   pra criar a autenticação `'acesso-admin'`.
 * 
 * ⚠️ IMPORTANTE
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ No exemplo, o método `validarCredenciais()` é só uma representação de     │
 * │ validação do login para melhor entendimento. Este tipo de implementação   │
 * │ não é nativo do framework, ele deve ser feito pela aplicação.             │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * 🖥️ EXEMPLO DE CONTROLLER EM /app/controllers/Login.php
 * ┌──────────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                                    │
 * │                                                                          │
 * │ namespace app\controllers;                                               │
 * │                                                                          │
 * │ use galastri\modules\Authentication;                                     │
 * │ use galastri\modules\Controller;                                         │
 * │ use galastri\modules\HttpData;                                           │
 * │ use galastri\modules\Redirect;                                           │
 * │                                                                          │
 * │ final class Login extends Controller                                     │
 * │ {                                                                        │
 * │     protected function main(): array                                     │
 * │     {                                                                    │
 * │         if (Authentication::validate('acesso-admin')) {                  │
 * │             Redirect::to('/painel-admin');                               │
 * │         }                                                                │
 * │                                                                          │
 * │         return [];                                                       │
 * │     }                                                                    │
 * │                                                                          │
 * │     protected function autenticar(): array                               │
 * │     {                                                                    │
 * │         $email = (string) HttpData::getValue('email');                   │
 * │         $senha = (string) HttpData::getValue('senha');                   │
 * │         $manterConectado = (bool) HttpData::getValue('manterConectado'); │
 * │                                                                          │
 * │         $usuario = $this->validarCredenciais($email, $senha);            │
 * │                                                                          │
 * │         if ($usuario === null) {                                         │
 * │             return ['erro' => 'E-mail ou senha inválidos.'];             │
 * │         }                                                                │
 * │                                                                          │
 * │         $duracao = 86400;                                                │
 * │                                                                          │
 * │         if ($manterConectado) {                                          │
 * │             $duracao = 86400 * 7;                                        │
 * │         }                                                                │
 * │                                                                          │
 * │         Authentication::register(                                        │
 * │             authTag: 'acesso-admin',                                     │
 * │             fields: [                                                    │
 * │                 'id' => $usuario['id'],                                  │
 * │                 'nome' => $usuario['nome'],                              │
 * │                 'perfil' => $usuario['perfil'],                          │
 * │             ],                                                           │
 * │             expiration: $duracao                                         │
 * │         );                                                               │
 * │                                                                          │
 * │         Redirect::to('/painel-admin');                                   │
 * │         return [];                                                       │
 * │     }                                                                    │
 * │ }                                                                        │
 * └──────────────────────────────────────────────────────────────────────────┘
 *
 * O que acontece aqui na prática:
 *
 * 1. Em `main()`, usamos `Authentication::validate('acesso-admin')` diretamente
 *    porque `/login` é uma rota pública. Assim, evitamos que alguém já
 *    autenticado veja o formulário de login de novo.
 *
 * 2. Em `autenticar()`, a aplicação verifica e-mail e senha por conta própria.
 *    Se estiver tudo certo, escolhemos o prazo de validade (24 horas por padrão
 *    ou 7 dias se "manter conectado" vier marcado) e chamamos
 *    `Authentication::register()`.
 *
 *    Esse prazo vale para a autenticação e seu cookie. Pra que a opção "manter
 *    conectado" funcione também depois de fechar e abrir o navegador, a
 *    configuração da sessão PHP precisa permitir que seu cookie e os dados
 *    guardados no servidor continuem disponíveis pelo período desejado.
 *    Aumentar apenas o prazo da autenticação não configura essa continuidade.
 *
 * 3. Os dados de `id`, `nome` e `perfil` ficam guardados na sessão PHP no
 *    servidor, enquanto o navegador recebe apenas o cookie `'acesso-admin'` com
 *    o token. Como mantivemos `regenerateIdSession` no valor padrão `true`, o
 *    identificador da sessão também é renovado automaticamente por segurança.
 *
 * 4. Chamamos `Authentication::register()` antes de `Redirect::to()`. Isso é
 *    importante porque `Redirect::to()` envia o redirecionamento e encerra a
 *    execução imediatamente com `exit()`. Registrando antes, o cookie e a
 *    sessão já ficam gravados antes do navegador seguir para `/painel-admin`.
 */



/**
 * 🟢 CASO 2: LENDO E ATUALIZANDO DADOS DA SESSÃO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Agora estamos dentro de `/painel-admin`. Como essa rota foi configurada com
 * `'authTag' => 'acesso-admin'`, o framework já validou a autenticação antes de
 * instanciar `/app/controllers/PainelAdmin.php`.
 *
 * Por isso, dentro dos métodos desse controller podemos consultar e atualizar
 * os campos guardados na sessão usando `getField()` e `setField()`, sem
 * precisar consultar o banco de dados a cada página só pra saber quem está
 * logado.
 * 
 * ⚠️ IMPORTANTE
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ No exemplo, o método `salvarPerfilNoBanco()` é só uma representação de    │
 * │ gravação de dados para melhor entendimento. Este tipo de implementação    │
 * │ não é nativo do framework, ele deve ser feito pela aplicação.             │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * 🖥️ EXEMPLO DE CONTROLLER EM /app/controllers/PainelAdmin.php
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                                     │
 * │                                                                           │
 * │ namespace app\controllers;                                                │
 * │                                                                           │
 * │ use galastri\modules\Authentication;                                      │
 * │ use galastri\modules\Controller;                                          │
 * │ use galastri\modules\HttpData;                                            │
 * │                                                                           │
 * │ final class PainelAdmin extends Controller                                │
 * │ {                                                                         │
 * │     protected function main(): array                                      │
 * │     {                                                                     │
 * │         $nome = Authentication::getField('nome', 'acesso-admin');         │
 * │         $perfil = Authentication::getField('perfil', 'acesso-admin');     │
 * │                                                                           │
 * │         return [                                                          │
 * │             'nome' => $nome,                                              │
 * │             'perfil' => $perfil,                                          │
 * │         ];                                                                │
 * │     }                                                                     │
 * │                                                                           │
 * │     protected function perfil(): array                                    │
 * │     {                                                                     │
 * │         $tema = Authentication::getField('tema', 'acesso-admin');         │
 * │                                                                           │
 * │         if ($tema === null) {                                             │
 * │             $tema = 'claro';                                              │
 * │         }                                                                 │
 * │                                                                           │
 * │         return [                                                          │
 * │             'nome' => Authentication::getField('nome', 'acesso-admin'),   │
 * │             'tema' => $tema,                                              │
 * │         ];                                                                │
 * │     }                                                                     │
 * │                                                                           │
 * │     protected function atualizarPerfil(): array                           │
 * │     {                                                                     │
 * │         $usuarioId = Authentication::getField('id', 'acesso-admin');      │
 * │         $novoNome = trim((string) HttpData::getValue('nome'));            │
 * │         $novoTema = trim((string) HttpData::getValue('tema'));            │
 * │                                                                           │
 * │         if ($novoNome === '') {                                           │
 * │             return ['erro' => 'Informe um nome válido.'];                 │
 * │         }                                                                 │
 * │                                                                           │
 * │         // A aplicação grava os novos dados no banco de dados.            │
 * │         $this->salvarPerfilNoBanco($usuarioId, $novoNome, $novoTema);     │
 * │                                                                           │
 * │         Authentication::setField('nome', $novoNome, 'acesso-admin');      │
 * │         Authentication::setField('tema', $novoTema, 'acesso-admin');      │
 * │                                                                           │
 * │         return [                                                          │
 * │             'nome' => $novoNome,                                          │
 * │             'tema' => $novoTema,                                          │
 * │             'salvo' => true,                                              │
 * │         ];                                                                │
 * │     }                                                                     │
 * │ }                                                                         │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * O que esse exemplo mostra:
 *
 * 1. Em `main()`, lemos `'nome'` e `'perfil'` que foram gravados lá em
 *    `Authentication::register()` e os enviamos direto para a view exibir.
 *
 * 2. Em `perfil()`, tentamos ler `'tema'`. Como esse campo não foi passado no
 *    registro inicial, `getField()` retorna `null`. Aproveitamos isso pra
 *    definir `'claro'` como valor padrão enquanto o usuário ainda não escolheu
 *    um tema.
 *
 * 3. Em `atualizarPerfil()`, após salvar no banco usando o `'id'` recuperado da
 *    sessão, chamamos `setField()` duas vezes: uma pra atualizar `'nome'` (que
 *    já existia) e outra pra criar `'tema'` (que ainda não existia). Lembre-se
 *    de que `setField()` aceita apenas `string`, `int`, `bool` ou `null`.
 *
 * 4. Numa requisição `GET` em `/painel-admin/perfil`, o framework executa
 *    `perfil()`. Já numa requisição `POST`, a configuração `onHttpMethod`
 *    direciona a execução diretamente para o método `atualizarPerfil()`.
 */



/**
 * 🟢 CASO 3: RENOVANDO OU AJUSTANDO O PRAZO DA AUTENTICAÇÃO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Imagine que o usuário está trabalhando no painel e não queremos que a
 * autenticação dele expire no meio de uma tarefa. Enquanto ele continua ativo,
 * a aplicação pode chamar o endpoint `/painel-admin/renovar` (ou oferecer um
 * botão pra renovar o acesso) e redefinir o prazo da autenticação com
 * `setExpiration()`.
 *
 * Assim, mantemos o mesmo token e todos os campos guardados na sessão (`id`,
 * `nome`, `perfil`, `tema`), atualizando apenas a data limite da autenticação e
 * reenviando o cookie ao navegador com o novo prazo.
 *
 * 🖥️ EXEMPLO DO MÉTODO EM /app/controllers/PainelAdmin.php
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ // Em /app/controllers/PainelAdmin.php                                    │
 * │                                                                           │
 * │ protected function renovar(): array                                       │
 * │ {                                                                         │
 * │     Authentication::setExpiration(7200, 'acesso-admin');            │
 * │                                                                           │
 * │     return [                                                              │
 * │         'renovado' => true,                                               │
 * │         'expiraEmSegundos' => 7200,                                       │
 * │     ];                                                                    │
 * │ }                                                                         │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * 💡 OBSERVAÇÃO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Lembre-se de que `setExpiration()` não soma ao prazo que já         │
 * │ restava: ele define um novo prazo contado a partir do momento da chamada, │
 * │ substituindo o anterior.                                                  │
 * │                                                                           │
 * │ Se o usuário havia entrado com "manter conectado" (7 dias), mas queremos  │
 * │ reduzir a validade pra 15 minutos, basta passar `900`:                    │
 * │                                                                           │
 * │ Authentication::setExpiration(900, 'acesso-admin');                 │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Como `/painel-admin/renovar` já está protegido por `'authTag' =>
 * 'acesso-admin'`, temos garantia de que a tag já está registrada na sessão
 * quando `renovar()` executa. Se chamássemos `setExpiration()` em uma
 * rota pública para uma tag que não existe na sessão, o método lançaria uma
 * exceção.
 */



/**
 * 🟢 CASO 4: LOGOUT SELETIVO E LOGOUT COMPLETO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Quando o usuário decide sair, temos duas opções conforme o comportamento que
 * queremos dar à aplicação: encerrar apenas uma área específica com `remove()`
 * ou encerrar todas as autenticações da sessão de uma só vez com `removeAll()`.
 *
 * Imagine que o usuário está autenticado ao mesmo tempo em `/painel-admin` (com
 * `'acesso-admin'`) e em `/area-cliente` (com `'acesso-cliente'`), e também tem
 * itens salvos em `$_SESSION['carrinho']`.
 *
 * 🖥️ EXEMPLO 1: SAINDO APENAS DO PAINEL COM remove()
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ // Em /app/controllers/PainelAdmin.php                                    │
 * │                                                                           │
 * │ ...                                                                       │
 * │                                                                           │
 * │ use galastri\modules\Redirect;                                            │
 * │                                                                           │
 * │ ...                                                                       │
 * │                                                                           │
 * │ protected function sair(): array                                          │
 * │ {                                                                         │
 * │     Authentication::remove('acesso-admin');                               │
 * │                                                                           │
 * │     Redirect::to('/login');                                               │
 * │     return [];                                                            │
 * │ }                                                                         │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Aqui, apenas a autenticação `'acesso-admin'` é apagada da sessão e marcada
 * pra expirar no navegador. Se o usuário acessar `/area-cliente`, ele continua
 * autenticado normalmente, e os dados de `$_SESSION['carrinho']` também ficam
 * preservados.
 *
 * Já se a aplicação oferecer um botão de "Sair de todas as áreas", podemos
 * criar um endpoint e chamar `removeAll()`:
 *
 * 🖥️ EXEMPLO 2: SAINDO DE TODAS AS AUTENTICAÇÕES COM removeAll()
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ ...                                                                       │
 * │                                                                           │
 * │ use galastri\modules\Redirect;                                            │
 * │                                                                           │
 * │ ...                                                                       │
 * │                                                                           │
 * │ protected function sairDeTudo(): array                                    │
 * │ {                                                                         │
 * │     Authentication::removeAll();                                          │
 * │                                                                           │
 * │     Redirect::to('/');                                                    │
 * │     return [];                                                            │
 * │ }                                                                         │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Neste segundo caso, tanto `'acesso-admin'` quanto `'acesso-cliente'` (e
 * qualquer outra tag registrada em `gAuth`) são removidas da sessão e têm seus
 * cookies marcados pra expirar. Ainda assim, `$_SESSION['carrinho']` e os
 * demais cookies da aplicação continuam intactos.
 *
 * Assim como em `register()`, chamamos `remove()` e `removeAll()` antes de
 * `Redirect::to()`, pra garantir que os cabeçalhos dos cookies expirados sejam
 * preparados antes de encerrar a requisição com o redirecionamento.
 */



/**
 * 🟢 CASO 5: PROTEGENDO UMA API JSON E CONFERÊNCIA DE IP
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Por fim, vamos olhar pra rota `/api/dados` que configuramos na Parte 1 com
 * `'output' => 'json'`, `'authTag' => 'acesso-admin'` e `'authFailMessage'`.
 *
 * Quando a saída é `json`, o framework não redireciona em caso de falha de
 * autenticação, mesmo que `authFailRedirectTo` esteja definido. Em vez disso,
 * se o usuário não tiver uma autenticação válida, o `Galastri` lança uma
 * exceção com o texto de `authFailMessage` antes mesmo de chamar o controller,
 * resultando em uma resposta de erro com status HTTP `403`.
 *
 * Se a autenticação automática passar, podemos ainda reforçar a proteção dentro
 * de `/app/controllers/Api.php` ativando a conferência de IP com o segundo
 * parâmetro de `validate()`:
 *
 * 🖥️ EXEMPLO DE CONTROLLER EM /app/controllers/Api.php
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ <?php                                                                     │
 * │                                                                           │
 * │ namespace app\controllers;                                                │
 * │                                                                           │
 * │ use galastri\modules\Authentication;                                      │
 * │ use galastri\modules\Controller;                                          │
 * │                                                                           │
 * │ final class Api extends Controller                                        │
 * │ {                                                                         │
 * │     protected function main(): array                                      │
 * │     {                                                                     │
 * │         return [];                                                        │
 * │     }                                                                     │
 * │                                                                           │
 * │     protected function dados(): array                                     │
 * │     {                                                                     │
 * │         if (!Authentication::validate('acesso-admin', ipCheck: true)) {   │
 * │             http_response_code(403);                                      │
 * │                                                                           │
 * │             return [                                                      │
 * │                 'erro' => 'Endereço IP diferente do login inicial.',      │
 * │             ];                                                            │
 * │         }                                                                 │
 * │                                                                           │
 * │         $nome = Authentication::getField('nome', 'acesso-admin');         │
 * │                                                                           │
 * │         return [                                                          │
 * │             'usuario' => $nome,                                           │
 * │             'itens' => ['Relatório A', 'Relatório B'],                    │
 * │         ];                                                                │
 * │     }                                                                     │
 * │ }                                                                         │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Na chamada automática feita pelo núcleo do framework, `validate()` roda com
 * `$ipCheck = false`, pra que o usuário não seja derrubado só porque trocou de
 * rede (por exemplo, do Wi-Fi pro 4G).
 *
 * Já em `dados()`, passamos `true` no segundo parâmetro. Com isso, exigimos que
 * o endereço IP da requisição atual seja o mesmo que foi salvo lá no momento do
 * `register()`. Se for diferente, respondemos com status `403` e uma mensagem
 * explicando o motivo.
 *
 * 💡 OBSERVAÇÃO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Use a conferência de IP com cautela: em aplicações acessadas por redes    │
 * │ móveis ou atrás de proxies que alternam o endereço em `REMOTE_ADDR`,      │
 * │ usuários legítimos podem acabar sendo bloqueados.                         │
 * └───────────────────────────────────────────────────────────────────────────┘
 */



/**
 * 🟢 RESUMO DO FLUXO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * - `Authentication::register()`: cria a tag na sessão e envia o cookie.
 * 
 * - `Authentication::validate()`: confere sessão, prazo, cookie e (se pedido) o
 *   IP.
 * 
 * - `Authentication::getField()` / `setField()`: lê e atualiza dados extras da
 *   sessão.
 * 
 * - `Authentication::setExpiration()`: redefine a validade a partir de
 *   agora.
 * 
 * - `Authentication::remove()` / `removeAll()`: encerra uma tag específica ou
 *   todas as autenticações da sessão atual.
 */


/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ◀️ VOLTAR AO TÓPICO ANTERIOR:
 * 🔗 LINK: /galastri/modules/Authentication.php
 */
