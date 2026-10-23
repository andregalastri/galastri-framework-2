```md
GALASTRI FRAMEWORK > ESTRUTURA E FUNCIONAMENTO
--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                                 INICIALIZAÇÃO                                ║
╚══════════════════════════════════════════════════════════════════════════════╝

Sempre que há uma requisição nova, antes de qualquer processamento de dados no
controller, o framework precisa fazer uma série de preparações. Esse processo
começa em `public/index.php` e se repete a cada requisição encaminhada ao
framework.

A sequência abaixo apresenta as principais etapas, desde a entrada da requisição
até a preparação da resposta:

┌────────────────────────────────────┬─────────────────────────────────────────┐
│ _ETAPA_                            │ _RESPONSABILIDADE_                      │
├────────────────────────────────────┼─────────────────────────────────────────┤
│ `public/index.php`                 │ Inicia a execução e carrega o           │
│                                    │ bootstrap.                              │
├────────────────────────────────────┼─────────────────────────────────────────┤
│ `galastri/bootstrap/bootstrap.php` │ Prepara os recursos básicos e ativa o   │
│                                    │ gerenciador de erros.                   │
├────────────────────────────────────┼─────────────────────────────────────────┤
│ `Galastri::run()`                  │ Coordena o processamento da requisição. │
├────────────────────────────────────┼─────────────────────────────────────────┤
│ `Config::run()`                    │ Carrega as definições de configuração.  │
├────────────────────────────────────┼─────────────────────────────────────────┤
│ `Router::run()`                    │ Identifica a rota e aplica suas         │
│                                    │ configurações.                          │
├────────────────────────────────────┼─────────────────────────────────────────┤
│ `Controller`                       │ Executa as operações da aplicação e     │
│                                    │ reúne os dados da resposta.             │
├────────────────────────────────────┼─────────────────────────────────────────┤
│ `Classe de saída`                  │ Prepara e envia o resultado no formato  │
│                                    │ configurado.                            │
└────────────────────────────────────┴─────────────────────────────────────────┘

Essa é a sequência de uma requisição atendida normalmente por um controller.
Redirecionamentos, erros e algumas respostas de arquivos podem seguir caminhos
diferentes, conforme explicado adiante.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                             ENTRADA DA REQUISIÇÃO                            ║
╚══════════════════════════════════════════════════════════════════════════════╝

O arquivo `public/index.php` é o ponto de entrada do framework. As regras do
servidor encaminham as requisições para esse arquivo, permitindo que o framework
identifique a rota e execute o código correspondente.

✔️
┌──────────────────────────────────────────────────────────────────────────────┐
│ _EXEMPLO_                                                                    │
│                                                                              │
│ Considere uma requisição para a seguinte URL                                 │
│ http://localhost/clientes/listar                                             │
│                                                                              │
│ Internamente, o servidor encaminha essa requisição para `public/index.php`.  │
│ O endereço continua sendo `/clientes/listar` no navegador, e o framework     │
│ utiliza esse caminho para identificar a rota.                                │
│                                                                              │
│ Esse encaminhamento interno é diferente de um redirecionamento HTTP, no qual │
│ o navegador recebe uma instrução para acessar outro endereço.                │
└──────────────────────────────────────────────────────────────────────────────┘
\


#                          ENCAMINHAMENTO PELO APACHE                          <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Na instalação com Apache, as regras dentro de `public/.htaccess` fazem com que o
servidor tenha um comportamento diferente do padrão.

*  A regra principal encaminha para `index.php` todas as requisições que não
   correspondem a um diretório existente.

*  URLs com barra no final são redirecionadas para a versão sem essa barra,
   exceto quando correspondem a diretórios existentes.

*  Requisições para `/favicon.ico` são atendidas diretamente pelo Apache, sem
   iniciar o framework. Se o arquivo não existir na pasta pública, o Apache
   retorna uma resposta 404.

Por isso, nem toda requisição recebida pelo servidor executa o código do
Galastri Framework.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                                   BOOTSTRAP                                  ║
╚══════════════════════════════════════════════════════════════════════════════╝

O bootstrap é a etapa que prepara os recursos básicos para a execução do
framework. É nesse momento que ficam disponíveis as constantes, as funções
auxiliares, o carregamento das classes e o gerenciador de erros.

Atualmente essa preparação é organizada em três arquivos dentro da pasta
`galastri/bootstrap`:

┌─────────────────┬────────────────────────────────────────────────────────────┐
│ _ARQUIVO_       │ _RESPONSABILIDADE_                                         │
├─────────────────┼────────────────────────────────────────────────────────────┤
│ `bootstrap.php` │ Coordena a preparação e inicia o processamento da          │
│                 │ requisição.                                                │
├─────────────────┼────────────────────────────────────────────────────────────┤
│ `constants.php` │ Define caminhos, informações de versão e valores           │
│                 │ utilizados pelo código.                                    │
├─────────────────┼────────────────────────────────────────────────────────────┤
│ `functions.php` │ Disponibiliza funções auxiliares.                          │
└─────────────────┴────────────────────────────────────────────────────────────┘

Além desses arquivos, a aplicação pode possuir seu próprio `app/bootstrap.php`,
executado depois da preparação básica e antes do processamento das configurações
e das rotas.



--------------------------------------------------------------------------------
▶️ PRÓXIMO TÓPICO: Processamento da requisição
LINK: /docs/estrutura-e-funcionamento/processamento-da-requisicao.md



---

👾 Esta documentação está sendo gerada com auxílio de IA...
🙋‍♂️ ...mas revisada sempre por um humano!
