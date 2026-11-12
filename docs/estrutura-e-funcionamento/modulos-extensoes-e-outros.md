```php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                        MÓDULOS, EXTENSÕES E OUTROS                        ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 *
 * Esta página reúne os módulos, as extensões e outros arquivos do Galastri
 * Framework. Use o índice pra encontrar a classe que você quer conhecer ou
 * consultar.
 */


/**
 * 🟢 SOBRE MÓDULOS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Os módulos são classes do framework que oferecem ferramentas que podemos usar
 * tanto no desenvolvimento da aplicação quanto no próprio framework.
 * 
 * Os módulos ficam localizados em `/galastri/modules`.
 *
 *
 * 🔶 O QUE É UM MÓDULO?
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Na filosofia de programação do framework é entendido que uma classe é
 * considerada um módulo se:
 * 
 * - Oferece métodos para usos gerais, como manipulação de arquivos, análise de
 *   debug ou performance, validação e processamento de dados, etc.
 * 
 * - Pode ou deve ser usada pelo usuário do framework.
 * 
 * - Pode ou deve ser usada pelo próprio framework, podendo ser fundamental
 *   para seu funcionamento ou não.
 *
 * Imagine que a aplicação precise criar uma pasta pra guardar os arquivos.
 * Podemos chamar o módulo `Tools` pra criar essa pasta usando o método
 * `createDir()`.
 *
 * Há casos em que o framework depende de um módulo pra executar uma etapa. Os
 * controllers que criamos em `app/controllers`, por exemplo, precisam herdar do
 * módulo `Controller`.
 * 
 * 
 * 🔶 ÍNDICE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Cada item a seguir aponta pra classe principal do módulo. Classes adicionais
 * que cuidam de operações específicas ficam listadas ou referenciadas na
 * documentação das classes principais.
 * 
 *
 * ✱ Authentication.php
 *   ───────────────────────────────────────────────────────────────────────────
 *   Cria e valida autenticações usando sessões e cookies.  
 *   🔗 LINK: /galastri/modules/Authentication.php
 *
 * 
 * ✱ Controller.php
 *   ───────────────────────────────────────────────────────────────────────────
 *   Serve de base para os controllers da aplicação. Chama os métodos do
 *   controller e reúne os dados que eles devolvem pra classe de saída.  
 *   🔗 LINK: /galastri/modules/Controller.php
 *
 * 
 * ✱ Database.php
 *   ───────────────────────────────────────────────────────────────────────────
 *   Cria os objetos de acesso a bancos de dados MySQL e SQLite.  
 *   🔗 LINK: /galastri/modules/Database.php
 *
 * 
 * ✱ HttpData.php
 *   ───────────────────────────────────────────────────────────────────────────
 *   Permite consultar os dados enviados depois do `?` na URL, em formulários
 *   POST ou no corpo da requisição em JSON.
 *   🔗 LINK: /galastri/modules/HttpData.php
 *
 * 
 * ✱ Permission.php
 *   ───────────────────────────────────────────────────────────────────────────
 *   Verifica se o usuário tem todas ou pelo menos uma das permissões exigidas,
 *   conforme a regra que a aplicação escolher.  
 *   🔗 LINK: /galastri/modules/Permission.php
 *
 * 
 * ✱ Redirect.php
 *   ───────────────────────────────────────────────────────────────────────────
 *   Módulo para redirecionamento do frontend pra outro endereço.  
 *   🔗 LINK: /galastri/modules/Redirect.php
 *
 * 
 * ✱ ResponseStream.php
 *   ───────────────────────────────────────────────────────────────────────────
 *   Envia atualizações ao frontend enquanto o controller ainda está processando
 *   uma requisição com saída `json`.  
 *   🔗 LINK: /galastri/modules/ResponseStream.php
 *
 * 
 * ✱ Tools.php
 *   ───────────────────────────────────────────────────────────────────────────
 *   Reúne métodos auxiliares pra lidar com arquivos, pastas, textos e arrays.  
 *   🔗 LINK: /galastri/modules/Tools.php
 *
 * 
 * ✱ Validation.php
 *   ───────────────────────────────────────────────────────────────────────────
 *   Disponibiliza validadores de textos, números, datas e horários.  
 *   🔗 LINK: /galastri/modules/Validation.php
 *
 * 
 * ✱ VarDump.php
 *   ───────────────────────────────────────────────────────────────────────────
 *   Módulo de depuração com formatação HTML.  
 *   🔗 LINK: /galastri/modules/VarDump.php
 */



/**
 * 🟢 SOBRE EXTENSÕES
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * As extensões são classes internas do framework que estendem classes padrão do
 * PHP, através de herança, pra ajustar ou acrescentar comportamentos.
 * 
 * As extensões ficam localizadas em `/galastri/extensions`.
 *
 *
 * 🔶 O QUE É UMA EXTENSÃO?
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Na filosofia de programação do framework é entendido que uma classe é uma
 * extensão se:
 *
 * - Herda uma classe padrão do PHP com o intuito de realizar alterações ou
 *   melhorias em seu funcionamento.
 *
 * - Tem como objetivo substituir a classe padrão do PHP para ser usada no
 *   framework.
 *
 * - É voltada para uso no framework, não sendo uma ferramenta voltada a
 *   usuários, apesar de poderem utilizá-la.
 *
 * A classe `Exception` do framework, por exemplo, estende as funcionalidades de
 * `\Exception` do PHP.
 *
 * ⚠️ IMPORTANTE
 * ┌────────────────────────────────────────────────────────────────────────┐
 * │ É importante ressaltar que aqui não se está falando sobre extensões no │
 * │ sentido de bibliotecas ou códigos criados por terceiros. Nestes casos, │
 * │ deve-se usar a inclusão via Composer.                                  │
 * └────────────────────────────────────────────────────────────────────────┘
 *
 *
 * 🔶 ÍNDICE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ✱ Exception.php
 *   ───────────────────────────────────────────────────────────────────────────
 *   Pode preencher a mensagem da exceção com os dados da falha e guardar
 *   informações adicionais sobre o erro.  
 *   🔗 LINK: /galastri/extensions/Exception.php
 */



/**
 * 🟢 OUTROS ARQUIVOS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ✱ Message.php
 *   ───────────────────────────────────────────────────────────────────────────
 *   Guarda os textos das mensagens de erro do framework. Quando precisamos
 *   de um desses textos, consultamos o catálogo pelo nome da mensagem.  
 *   🔗 LINK: /galastri/language/Message.php
 */



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ◀️ VOLTAR AO TÓPICO ANTERIOR:
 * 🔗 LINK: /galastri/core/output/File.php
 */
