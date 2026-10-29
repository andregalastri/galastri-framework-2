```php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                         ESTRUTURA E FUNCIONAMENTO                         ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 *
 * O Galastri Framework se organiza em múltiplas pastas com objetivos bem
 * definidos, sendo as principais a pasta `app`, que é onde a aplicação será
 * desenvolvida, e a pasta `galastri`, onde estão os arquivos do framework em
 * si.
 *
 * A pasta `galastri` reúne o núcleo e todas as ferramentas e funcionalidades
 * que organizam o fluxo de dados. Este fluxo, por padrão, é fortemente
 * relacionado com a configuração de rotas, ou seja, o caminho para onde a URL
 * está apontando.
 *
 * Essa relação entre o endereço acessado e o fluxo do dado dentro do framework
 * é o que torna o funcionamento do framework muito mais compreensível.
 */



/**
 * 🟢 ARQUITETURA
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * O framework utiliza uma organização chamada `Thin Model MVC`, uma variante do
 * MVC em que `Controllers` e `Views` possuem estruturas e funcionalidades
 * próprias, enquanto que a organização dos `Models` fica totalmente a cargo da
 * aplicação.
 *
 * ┌──────────────┬────────────────────────────────────────────────────────────┐
 * │ COMPONENTE   │ RESPONSABILIDADE                                           │
 * ├──────────────┼────────────────────────────────────────────────────────────┤
 * │ `Controller` │ Processa a requisição, utiliza os recursos necessários e   │
 * │              │ reúne os dados da resposta.                                │
 * ├──────────────┼────────────────────────────────────────────────────────────┤
 * │ `View`       │ Apresenta os dados em uma interface de saída, como HTML,   │
 * │              │ JSON ou a renderização de um arquivo.                      │
 * ├──────────────┼────────────────────────────────────────────────────────────┤
 * │ `Model`      │ O framework não exige uma estrutura específica para        │
 * │              │ Models. Você pode criar classes próprias para essas        │
 * │              │ responsabilidades e utilizá-las através dos controllers    │
 * └──────────────┴────────────────────────────────────────────────────────────┘
 *
 *
 *
 * 🔶 ROTAS E CONTROLLERS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * As rotas são organizadas através da divisão da URL em `nós` e `endpoints`.
 * Cada `/` da URL representa o local de quebra, separando os termos do caminho.
 *
 * O `nó` é a parte desse caminho que representa uma classe controller, enquanto
 * que um endpoint é a parte do caminho que representa o método do controller
 * que será executado.
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Vamos imaginar uma URL `www.dominio.com/clientes/listar`. Esta rota é     │
 * │ dividida em duas partes:                                                  │
 * │                                                                           │
 * │ ┌───────────────┬───────────────────────────────────────────────────────┐ │
 * │ │ PARTE DA ROTA │ CORRESPONDÊNCIA                                       │ │
 * │ ├───────────────┼───────────────────────────────────────────────────────┤ │
 * │ │ `clientes`    │ O framework entende que se trata de um nó e relaciona │ │
 * │ │               │ esta parte à classe `Clientes`.                       │ │
 * │ ├───────────────┼───────────────────────────────────────────────────────┤ │
 * │ │ `listar`      │ É um endpoint por ser o último trecho da rota, sendo  │ │
 * │ │               │ relacionado a um método dentro da classe `Clientes`.  │ │
 * │ │               │ O nome do método, neste caso, é `listar()`.           │ │
 * │ └───────────────┴───────────────────────────────────────────────────────┘ │
 * │                                                                           │
 * │ O domínio é sempre ignorado nesta composição.                             │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Dentro desta premissa, a estrutura pode tornar-se mais complexa conforme o
 * caminho aumenta e vão existindo nós dentro de outros nós.
 *
 * 💡 OBSERVAÇÃO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ A organização das pastas dos controllers também segue a mesma estrutura   │
 * │ da rota. Detalharemos mais sobre isso na seção Rotas, com mais exemplos e │
 * │ explicações.                                                              │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 *
 *
 * 🔶 PROCESSAMENTO DA REQUISIÇÃO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * O método que representa o endpoint é o ponto inicial do processamento daquela
 * rota. É nele que os dados são processados de acordo com o que se deseja
 * responder na requisição. Pode-se processar qualquer coisa, ou simplesmente
 * não fazer nada. Vai da necessidade da aplicação.
 *
 * ✔️ EXEMPLO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Voltando à rota `/clientes/listar`, poderíamos fazer com que o método     │
 * │ `listar()` tenha um fluxo de processamento como:                          │
 * │                                                                           │
 * │ 1. Recebimento dos dados da consulta: Os dados são capturados via GET,    │
 * │ POST, ou JSON.                                                            │
 * │                                                                           │
 * │ 2. Validação destes dados: Testa-se se os dados (como termo de busca e    │
 * │ ordenação da lista) são válidos.                                          │
 * │                                                                           │
 * │ 3. Acesso ao banco de dados: Faz a consulta no banco de dados para trazer │
 * │ o resultado.                                                              │
 * │                                                                           │
 * │ 4. Organização do resultado: Organiza os dados em formato de lista que    │
 * │ vão retornar ao frontend.                                                 │
 * │                                                                           │
 * │ 5. Retorna: Conclui retornando o resultado para o controller que          │
 * │ prosseguirá com o fluxo até a resposta para o frontend.                   │
 * └───────────────────────────────────────────────────────────────────────────┘
 *
 * Os controllers, portanto, recebem e organizam qual método deverá ser chamado
 * e o método processa os dados para retornar ao controller, que prossegue com o
 * fluxo.
 *
 *
 *
 * 🔶 VIEWS E FORMATOS DE SAÍDA
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * O resultado do processamento no controller é entregue a um formato de saída.
 *
 * `Formato de saída` é o tipo de resposta que se espera receber após o
 * processamento daquela rota.
 *
 * Atualmente, o framework conta com três formatos de saída possíveis:
 * 
 * ┌────────┬──────────────────────────────────────────────────────────────────┐
 * │ SAÍDA  │ FINALIDADE                                                       │
 * ├────────┼──────────────────────────────────────────────────────────────────┤
 * │ `View` │ Monta uma página HTML utilizando os arquivos de interface da     │
 * │        │ aplicação.                                                       │
 * ├────────┼──────────────────────────────────────────────────────────────────┤
 * │ `Json` │ Envia os dados em JSON. Ideal para responder uma chamada         │
 * │        │ JavaScript ou uma API.                                           │
 * ├────────┼──────────────────────────────────────────────────────────────────┤
 * │ `File` │ Entrega um arquivo para exibição ou download.                    │
 * └────────┴──────────────────────────────────────────────────────────────────┘
 * 
 * ℹ️ IMPORTANTE
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Nesta documentação, o termo `view` pode indicar duas coisas:              │
 * │                                                                           │
 * │ * Um arquivo de interface, que é importado dentro de um template HTML.    │
 * │                                                                           │
 * │ * A saída `View`, classe responsável por preparar esse formato de saída   │
 * │ HTML. O contexto sempre vai indicar sobre qual dos dois está sendo        │
 * │ abordado.                                                                 │
 * └───────────────────────────────────────────────────────────────────────────┘
 */



/**
 * 🟢 PARADIGMA DE PROGRAMAÇÃO
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * O código segue uma organização orientada a classes chamada `Class-Oriented
 * Programming (COP)`.
 *
 * A ideia é que todas as funcionalidades DEVEM ser organizadas através de
 * classes. A diferença entre COP e OOP é que nesta abordagem não se exige que
 * todos os recursos sejam construídos com herança, polimorfismo ou outros
 * mecanismos da programação orientada a objetos.
 *
 * Em COP, podem existir tanto classes com métodos estáticos quanto classes que
 * precisam ser instanciadas. O que importa é isolar as propriedades e métodos
 * dentro de uma classe.
 *
 *
 *
 * 🔶 DIRETRIZES DE DESENVOLVIMENTO DO FRAMEWORK
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * -  Organizar as funcionalidades em classes.  
 *
 * -  Manter cada classe em seu próprio arquivo.  
 *
 * -  Priorizar a _composição_ ao invés de herança.  
 *
 * A herança pode ser utilizada em casos específicos. A classe `Exception` do
 * framework, por exemplo, estende a classe `Exception` do PHP. Mas para
 * desenvolver novos recursos, a preferência continua sendo a composição.
 *
 *
 *
 * 🔶 DIRETRIZES PARA DESENVOLVER APLICAÇÕES
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * -  Os controllers da aplicação devem estender a classe base
 *    `galastri\modules\Controller`.  
 * 
 * -  Os métodos que representam endpoints devem coordenar o processamento das
 *    operações correspondentes.  
 * 
 * -  Classes auxiliares podem organizar o acesso aos dados, regras e outras
 *    funcionalidades utilizadas pelos controllers. 
 *  
 * -  Essas classes auxiliares devem ficar em pastas próprias, separadas de
 *    `app/controllers`.  
 * 
 * -  Cada arquivo deve conter apenas uma classe.
 *
 *
 *
 * 🔶 REFERÊNCIAS E CARREGAMENTO DE ARQUIVOS DE CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Para referenciar uma classe de outro namespace por um nome mais curto, use a
 * palavra-chave `use`. Ela permite escrever, por exemplo, `Tools` em vez de
 * `\galastri\modules\Tools`.
 *
 * Quem localiza e carrega o arquivo da classe é o `autoload` do Composer, já
 * configurado no projeto. O `use` define a referência ao nome da classe, mas
 * não carrega seu arquivo.
 *
 * Este é um dos motivos para se encapsular todos os códigos em classes.
 *
 * Evite chamadas de `require` e `include` para carregar arquivos que fazem
 * parte do fluxo de processamento de dados que estão em classes.
 * 
 * ✔️ EXEMPLO VÁLIDO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Para referenciar `Tools`, informe seu namespace e nome da classe. O       │
 * │ autoload carrega o arquivo da classe quando ela é utilizada.              │
 * │                                                                           │
 * │ ┌───────────────────────────────────────────────────────────────────────┐ │
 * │ │ <?php                                                                 │ │
 * │ │ namespace app;                                                        │ │
 * │ │                                                                       │ │
 * │ │ use \galastri\modules\Tools;                                          │ │
 * │ └───────────────────────────────────────────────────────────────────────┘ │
 * └───────────────────────────────────────────────────────────────────────────┘
 * 
 * ❌ EXEMPLO INVÁLIDO
 * ┌───────────────────────────────────────────────────────────────────────────┐
 * │ Importar o arquivo `/galastri/modules/Tools.php` diretamente usando       │
 * │ `require` ou `include`                                                    │
 * │                                                                           │
 * │ ┌───────────────────────────────────────────────────────────────────────┐ │
 * │ │ <?php                                                                 │ │
 * │ │ namespace app;                                                        │ │
 * │ │                                                                       │ │
 * │ │ require '/galastri/modules/Tools.php';                                │ │
 * │ └───────────────────────────────────────────────────────────────────────┘ │
 * └───────────────────────────────────────────────────────────────────────────┘
 */



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ▶️ PRÓXIMO TÓPICO:
 * 🔗 LINK: /docs/estrutura-e-funcionamento/estrutura-de-pastas.md
 */ 
