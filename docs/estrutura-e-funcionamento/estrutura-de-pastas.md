```md
GALASTRI FRAMEWORK > ESTRUTURA E FUNCIONAMENTO
--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                             ESTRUTURA DE PASTAS                              ║
╚══════════════════════════════════════════════════════════════════════════════╝

O framework é subdividido em pastas, cada uma com uma função específica. A
estrutura da instalação segue o seguinte padrão:

📂 galastri-framework-2/
├── 📂 app/
│   ├── 📂 config/
│   ├── 📂 controllers/
│   ├── 📂 templates/
│   └── 📂 views/
│       └── 📂 Index/
├── 📂 galastri/
│   ├── 📂 bootstrap/
│   ├── 📂 core/
│   │   ├── 📂 config/
│   │   │   └── 📂 definitions/
│   │   └── 📂 output/
│   │       ├── 📂 engines/
│   │       └── 📂 traits/
│   ├── 📂 extensions/
│   ├── 📂 language/
│   ├── 📂 misc/
│   └── 📂 modules/
│       ├── 📂 database/
│       │   └── 📂 traits/
│       └── 📂 validation/
│           └── 📂 traits/
├── 📂 logs/
├── 📂 misc/
├── 📂 public/
└── 📂 vendor/



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                                  PASTA app/                                  ║
╚══════════════════════════════════════════════════════════════════════════════╝

Esta é a pasta da aplicação do usuário. É nela que ele trabalhará para criar
páginas, configurar rotas e implementar as operações do seu projeto.


#                             SUBPASTA app/config                              <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Contém os arquivos de configuração da aplicação. Os arquivos principais são:

┌──────────────────┬───────────────────────────────────────────────────────────┐
│ _ARQUIVO_        │ _FINALIDADE_                                              │
├──────────────────┼───────────────────────────────────────────────────────────┤
│ `database.php`   │ Configuração das conexões com bancos de dados.            │
├──────────────────┼───────────────────────────────────────────────────────────┤
│ `mime-types.php` │ Configuração dos tipos MIME, que identificam formatos de  │
│                  │ conteúdo, como HTML, imagens e arquivos.                  │
├──────────────────┼───────────────────────────────────────────────────────────┤
│ `routes.php`     │ Configuração das rotas e seus comportamentos.             │
└──────────────────┴───────────────────────────────────────────────────────────┘


#                           SUBPASTA app/controllers                           <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Contém os controllers da aplicação, responsáveis por processar as requisições. O
arquivo `Index.php`, que acompanha a instalação, contém o controller da página
inicial de exemplo.

Os controllers devem estender a classe `galastri\modules\Controller`. Sua
localização acompanha o namespace utilizado no código.

Por exemplo, a classe `Index`, no namespace `app\controllers`, corresponde ao
arquivo `app/controllers/Index.php`.

⚠️
┌──────────────────────────────────────────────────────────────────────────────┐
│ Letras maiúsculas e minúsculas fazem diferença nos nomes dos arquivos e das  │
│ pastas. Os nomes dos namespaces e das classes devem corresponder aos         │
│ respectivos caminhos.                                                        │
└──────────────────────────────────────────────────────────────────────────────┘
>


#                      SUBPASTA app/templates E app/views                      <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Essas duas pastas guardam os arquivos utilizados para montar as páginas HTML.

Um template é um arquivo `.php` que contém uma estrutura HTML com o objetivo de
compartilhar elementos que se repetem entre várias páginas da aplicação, como
cabeçalho, menus e rodapé. Já uma view contém o conteúdo da página que está
sendo exibida.

A instalação inicial inclui estes dois arquivos de exemplo:

* `app/templates/template.php`:  
  Contém a estrutura HTML utilizada pela página de exemplo.
  
* `app/views/Index/main.php`:  
  contém o conteúdo apresentado nessa página.


O uso de templates é opcional, mas é uma convenção comum em aplicações web. A
aplicação, porém, pode utilizar uma view sem compartilhar sua estrutura com
outras páginas.


#                    OUTRAS PASTAS E ARQUIVOS DA APLICAÇÃO                     <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
O usuário pode acrescentar pastas para organizar models e classes auxiliares
conforme a necessidade do projeto. No caso de classes, é necessário que fiquem
separadas dos controllers.

O framework não exige uma pasta específica para os models. Por isso, uma pasta
como `app/models/` pode ser criada se fizer parte do projeto, mas não acompanha
a instalação inicial.

Além das pastas mostradas, `app` contém um arquivo `VERSION`, que registra a
versão da aplicação de forma conveniente. O conteúdo deste arquivo pode ser
recuperado dentro da aplicação sempre que necessário.

Também existem dois caminhos opcionais reconhecidos pelo framework:

┌───────────────────────────┬──────────────────────────────────────────────────┐
│ _ARQUIVO/PASTA_           │ _FINALIDADE_                                     │
├───────────────────────────┼──────────────────────────────────────────────────┤
│ `app/bootstrap.php`       │ Executa uma preparação específica da aplicação   │
│                           │ durante a inicialização.                         │
├───────────────────────────┼──────────────────────────────────────────────────┤
│ `app/config/definitions/` │ Permite acrescentar definições de configuração   │
│ e seus arquivos.          │ próprias da aplicação.                           │
└───────────────────────────┴──────────────────────────────────────────────────┘

Esses caminhos não vêm na instalação inicial. Seu funcionamento será explicado
nos artigos correspondentes.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                               PASTA galastri/                                ║
╚══════════════════════════════════════════════════════════════════════════════╝

Enquanto a pasta `app` contém a aplicação, a pasta `galastri` reúne a
implementação do framework em si. É nesta pasta que estão os arquivos
responsáveis pelo processamento das rotas e preparação das respostas, além dos
módulos e dos demais recursos.

No desenvolvimento de aplicações, as alterações devem ficar concentradas em
`app`. Modificações em `galastri` alteram o próprio framework e precisam ser
preservadas ou conciliadas em caso de atualização de versão.


#                          SUBPASTA galastri/bootstrap                         <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Contém os arquivos que preparam o framework para executar uma requisição:

┌─────────────────┬────────────────────────────────────────────────────────────┐
│ _ARQUIVO_       │ _FINALIDADE_                                               │
├─────────────────┼────────────────────────────────────────────────────────────┤
│ `bootstrap.php` │ Coordena a inicialização do framework.                     │
├─────────────────┼────────────────────────────────────────────────────────────┤
│ `constants.php` │ Define as constantes utilizadas pelo framework.            │
├─────────────────┼────────────────────────────────────────────────────────────┤
│ `functions.php` │ Disponibiliza funções auxiliares.                          │
└─────────────────┴────────────────────────────────────────────────────────────┘

💡
┌──────────────────────────────────────────────────────────────────────────────┐
│ A sequência de execução desses arquivos será detalhada nos artigos,          │
│ "Inicialização" e "Bootstrap".                                               │
└──────────────────────────────────────────────────────────────────────────────┘
>


#                            SUBPASTA galastri/core                            <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Reúne as classes centrais do funcionamento do framework:

┌────────────────────┬─────────────────────────────────────────────────────────┐
│ _ARQUIVO_          │ _FINALIDADE_                                            │
├────────────────────┼─────────────────────────────────────────────────────────┤
│ `Galastri.php`     │ Coordena a execução do framework.                       │
├────────────────────┼─────────────────────────────────────────────────────────┤
│ `Router.php`       │ Realiza o processamento das rotas.                      │
├────────────────────┼─────────────────────────────────────────────────────────┤
│ `ErrorHandler.php` │ Realiza o tratamento e o registro de erros.             │
└────────────────────┴─────────────────────────────────────────────────────────┘

Dentro de `galastri/core`, há outras duas subdivisões: `galastri/core/config` e
`galastri/core/output`.


#                        SUBPASTA galastri/core/config                         <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Contém a implementação do sistema de configuração com as classes `Config` e
`Definition`.

A subpasta `definitions/` descreve as propriedades de configuração aceitas pelo
framework, seus tipos e valores padrão. Nela ficam as definições que serão
usadas pela aplicação no arquivo de configuração de rotas em
`app/config/routes.php`.

A diferença em relação à pasta `app/config` é a função de cada uma: as
definições descrevem o que pode ser configurado enquanto que os arquivos da
aplicação utilizam estas definições.


#                        SUBPASTA galastri/core/output                         <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Contém as classes responsáveis pelos três formatos de saída: `View`, `Json` e
`File`.

Dentro dela há as pastas abaixo:

┌────────────┬─────────────────────────────────────────────────────────────────┐
│ _SUBPASTA_ │ _FINALIDADE_                                                    │
├────────────┼─────────────────────────────────────────────────────────────────┤
│ `engines/` │ Reúne classes de template engines, responsáveis por renderizar  │
│            │ os arquivos de views. Atualmente há suporte apenas para o       │
│            │ templating do próprio PHP através da classe`PhpEngine`.         │
├────────────┼─────────────────────────────────────────────────────────────────┤
│ `traits/`  │ Reúne as traits com funcionalidades adicionais que são          │
│            │ compartilhadas entre as classes de formato.                     │
└────────────┴─────────────────────────────────────────────────────────────────┘


#                         SUBPASTA galastri/extensions                         <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Contém extensões de classes existentes. Atualmente, inclui a classe Exception do
framework, que estende a classe Exception do próprio PHP.


#                          SUBPASTA galastri/language                          <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Contém o arquivo Message.php, que centraliza as mensagens do framework,
atualmente em português.


#                            SUBPASTA galastri/misc                            <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Guarda arquivos de apoio utilizados pelo próprio framework, como a fonte e o
logotipo da tela de erros e o arquivo de estilos vardump.css.


#                           SUBPASTA galastri/modules                          <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Reúne os recursos que podem ser utilizados pela aplicação, como acesso ao banco
de dados, autenticação, permissões e validação entre outros.

💡
┌──────────────────────────────────────────────────────────────────────────────┐
│ Esta documentação irá detalhar cada uma dessas classes mais adiante.         │
└──────────────────────────────────────────────────────────────────────────────┘
>

Alguns módulos dividem sua implementação em arquivos adicionais:

┌─────────────────────┬────────────────────────────────────────────────────────┐
│ _SUBPASTA_          │ _FINALIDADE_                                           │
├─────────────────────┼────────────────────────────────────────────────────────┤
│ `database`          │ Contém as implementações de acesso ao MySQL e ao       │
│                     │ SQLite.                                                │
├─────────────────────┼────────────────────────────────────────────────────────┤
│ `database/traits`   │ Reúne trechos de implementação que podem ser           │
│                     │ reutilizados pelas variações do módulo Database.       │
├─────────────────────┼────────────────────────────────────────────────────────┤
│ `validation`        │ Contém as implementações de validação de textos,       │
│                     │ números, datas e horários.                             │
├─────────────────────┼────────────────────────────────────────────────────────┤
│ `validation/traits` │ Reúne trechos de implementação que podem ser           │
│                     │ reutilizados pelas variações do módulo Validation.     │
└─────────────────────┴────────────────────────────────────────────────────────┘


#                               ARQUIVOS VERSION                               <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
O arquivo galastri/VERSION registra a versão do framework. Ele é separado de
app/VERSION porque a aplicação e o framework possuem versões independentes.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                                  PASTA logs/                                 ║
╚══════════════════════════════════════════════════════════════════════════════╝

Esta pasta recebe os registros de erros produzidos pelo `ErrorHandler`. Esses
registros ajudam a investigar falhas durante o desenvolvimento e a execução da
aplicação.

O PHP-FPM precisa ter permissão de escrita nessa pasta. Essa permissão foi
configurada durante o tutorial de instalação.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                                  PASTA misc/                                 ║
╚══════════════════════════════════════════════════════════════════════════════╝

A pasta `misc/` da raiz guarda materiais de apoio do projeto, como o arquivo
`Galastri Framework Logo.svg`.

Não confundir com a pasta `galastri/misc/`.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                                 PASTA public/                                ║
╚══════════════════════════════════════════════════════════════════════════════╝

Esta é a pasta configurada como raiz do site no servidor web. No ambiente que
preparamos, ao acessar https://localhost, o Apache utiliza o caminho
`/home/desenvolvedor/galastri-framework-2/public`.

Ela contém três arquivos:

┌──────────────┬───────────────────────────────────────────────────────────────┐
│ _ARQUIVO_    │ _FINALIDADE_                                                  │
├──────────────┼───────────────────────────────────────────────────────────────┤
│ `index.php`  │ Recebe as requisições e inicia a execução do framework.       │
├──────────────┼───────────────────────────────────────────────────────────────┤
│ `.htaccess`  │ Configura as regras de encaminhamento das requisições no      │
│              │ Apache.                                                       │
├──────────────┼───────────────────────────────────────────────────────────────┤
│ `nginx.conf` │ Fornece regras para ambientes que utilizam Nginx no lugar do  │
│              │ Apache.                                                       │
│              │                                                               │
│              │ Essas regras precisam ser incorporadas à configuração         │
│              │ do servidor, junto das demais configurações do site e do      │
│              │ PHP-FPM.                                                      │
└──────────────┴───────────────────────────────────────────────────────────────┘

⚠️
┌──────────────────────────────────────────────────────────────────────────────┐
│ O arquivo `.htaccess` pode ficar oculto no gerenciador de arquivos por ter   │
│ um nome iniciado com ponto.                                                  │
│                                                                              │
│ Somente a pasta `public/` deve ficar exposta pelo servidor web. As demais    │
│ pastas é onde ficará o código fonte da aplicação, contendo configurações e   │
│ registros que não devem ser acessados diretamente pelo navegador.            │
│                                                                              │
│ As regras do `.htaccess` fornecido encaminham para `index.php` as            │
│ requisições que não correspondem a diretórios. Por isso, colocar um arquivo  │
│ em `public/` não significa que ele será entregue diretamente pelo Apache.    │
└──────────────────────────────────────────────────────────────────────────────┘
>



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                                 PASTA vendor/                                ║
╚══════════════════════════════════════════════════════════════════════════════╝

Esta pasta contém os arquivos gerenciados pelo Composer, incluindo o carregador
de classes `autoload.php`.

Mesmo que a instalação atual não tenha bibliotecas externas para baixar, esse
carregador é necessário. É ele permite encontrar as classes a partir dos
namespaces configurados pelo projeto, conforme abaixo:

┌─────────────┬────────────────────────────────────────────────────────────────┐
│ _NAMESPACE_ │ _FINALIDADE_                                                   │
├─────────────┼────────────────────────────────────────────────────────────────┤
│ `app\`      │ Permite buscar classes dentro de `app/`                        │
├─────────────┼────────────────────────────────────────────────────────────────┤
│ `galastri\` │ Permite buscar classes dentro de `galastri/`                   │
└─────────────┴────────────────────────────────────────────────────────────────┘

Os arquivos de vendor/ devem ser preparados pelos comandos do Composer.
Alterações manuais podem ser perdidas quando esses arquivos forem gerados
novamente.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                               ARQUIVOS DA RAIZ                               ║
╚══════════════════════════════════════════════════════════════════════════════╝

Além das pastas, a raiz do projeto contém os seguintes arquivos:

┌─────────────────┬────────────────────────────────────────────────────────────┐
│ _ARQUIVO_       │ _FINALIDADE_                                               │
├─────────────────┼────────────────────────────────────────────────────────────┤
│ `composer.json` │ Declara os requisitos do projeto e configura o             │
│                 │ carregamento das classes. Pode ser alterado conforme as    │
│                 │ necessidades da aplicação.                                 │
├─────────────────┼────────────────────────────────────────────────────────────┤
│ `composer.lock` │ Registra as versões das dependências resolvidas pelo       │
│                 │ Composer, utilizadas pelo comando`composer install`. É     │
│                 │ gerenciado pelo Composer e não deve ser editado            │
│                 │ manualmente.                                               │
├─────────────────┼────────────────────────────────────────────────────────────┤
│ `README.md`     │ Apresentação do framework.                                 │
├─────────────────┼────────────────────────────────────────────────────────────┤
│ `LICENSE`       │ Informa a licença de uso.                                  │
└─────────────────┴────────────────────────────────────────────────────────────┘
>



--------------------------------------------------------------------------------
▶️ PRÓXIMO TÓPICO

Sobre ambientes Windows:
LINK: /docs/estrutura-e-funcionamento/inicializacao.md



---

👾 Esta documentação está sendo gerada com auxílio de IA...
🙋‍♂️ ...mas revisada sempre por um humano!
