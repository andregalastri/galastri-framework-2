```md
GALASTRI FRAMEWORK > ESTRUTURA E FUNCIONAMENTO
--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                          PROCESSAMENTO DA REQUISIÇÃO                         ║
╚══════════════════════════════════════════════════════════════════════════════╝

Após a inicialização, a classe `Galastri`, localizada em
`galastri/core/Galastri.php`, é iniciada através do método `run()`.

Esta é a classe com a função de coordenar as etapas necessárias para atender a
requisição dentro de uma ordem específica.

1. _Carregamento das Definições:_
   Primeiro, `Config::run()` carrega as definições presentes em
   `galastri/core/config/definitions/` e `app/config/definitions/` (pasta
   opcional).

   Essas definições descrevem as configurações reconhecidas pelo
   framework, incluindo seus valores padrão e as regras de validação dos valores
   recebidos.

2. _Identificação da Rota:_
   Em seguida, `Router::run()` carrega `app/config/routes.php` e analisa o
   caminho da URL.

   Durante esse processamento, o Router identifica os nós, o endpoint e os
   parâmetros da rota, aplica as configurações correspondentes e determina o
   controller e o método que serão utilizados.

   A query string é separada antes da divisão do caminho. Assim, em um endereço
   como `/clientes/listar?pagina=2`, o trecho `?pagina=2` não é interpretado
   como parte do nome do endpoint.

3. _Aplicação das configurações_
   Com a rota identificada, a classe `Galastri` prossegue nesta ordem:

   *  Aplica o fuso horário configurado.
   
   *  Seleciona a classe responsável pelo formato de saída.
   
   *  Verifica se há um redirecionamento obrigatório.
   
   *  Verifica se a rota está marcada como indisponível.
   
   *  Verifica a validade da rota, considerando se a saída exige um controller.
   
   *  Verifica a autenticação, quando exigida.
   
   *  Localiza o controller e os métodos correspondentes.

   Essas verificações acontecem antes da execução das operações da aplicação. Um
   redirecionamento ou uma falha de autenticação, por exemplo, pode encerrar o
   atendimento sem executar o controller.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                            EXECUÇÃO DO CONTROLLER                            ║
╚══════════════════════════════════════════════════════════════════════════════╝

Quando há uma classe controller, `Galastri` cria uma instância dela. Essa
criação executa o construtor herdado de `galastri\modules\Controller`, que
coordena as chamadas aos métodos da aplicação.

A sequência é:

1. Executar o método correspondente ao endpoint.  

2. Executar o método específico do verbo HTTP, caso exista uma associação
   configurada para a requisição.  
   
3. Combinar os arrays retornados pelos métodos.  

4. Acrescentar os metadados do projeto, caso a configuração
   `includeProjectMetadata` esteja habilitada.  

O método associado ao verbo HTTP é executado _depois_ do método do endpoint,
não ocorre antes nem substitui a execução desse primeiro.

Os dados reunidos ficam disponíveis para a classe de saída preparar a resposta.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                            PREPARAÇÃO DA RESPOSTA                            ║
╚══════════════════════════════════════════════════════════════════════════════╝

Após a execução do controller, o framework chama o método `run()` da classe de
saída definida pela rota.

As classes de saída atuais são:

┌──────────┬───────────────────────────────────────────────────────────────────┐
│ _CLASSE_ │ _RESULTADO_                                                       │
├──────────┼───────────────────────────────────────────────────────────────────┤
│ `View`   │ Monta a página utilizando os arquivos de interface.               │
├──────────┼───────────────────────────────────────────────────────────────────┤
│ `Json`   │ Prepara a resposta em JSON.                                       │
├──────────┼───────────────────────────────────────────────────────────────────┤
│ `File`   │ Prepara a entrega de um arquivo.                                  │
└──────────┴───────────────────────────────────────────────────────────────────┘

💡
┌──────────────────────────────────────────────────────────────────────────────┐
│ A saída `File` também permite atender requisições sem um controller. Neste   │
│ caso, o processamento do arquivo é resolvido diretamente pelo mecanismo de   │
│ `File`.                                                                      │
└──────────────────────────────────────────────────────────────────────────────┘
>



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                       EXEMPLO DIDÁTICO: PÁGINA INICIAL                       ║
╚══════════════════════════════════════════════════════════════════════════════╝

A página que acompanha a instalação permite relacionar essa sequência aos
arquivos do projeto.

1. Ao acessar `http://localhost`, a requisição se inicia por `public/index.php`
   e passa pelo bootstrap.
   
2. Depois, o Router encontra a configuração da raiz `/` e o endpoint `@main` em
   `app/config/routes.php`.

3. É identificada cada parte da estrutura interna que vai fazer parte do fluxo:

   ┌────────────────────────────┬──────────────────────────────────────────────┐
   │ _PARTE_                    │ _ARQUIVO OU MÉTODO_                          │
   ├────────────────────────────┼──────────────────────────────────────────────┤
   │ `Controller a ser chamado` │ app/controllers/Index.php                    │
   ├────────────────────────────┼──────────────────────────────────────────────┤
   │ `Método do controller a`   │ Index::main()                                │
   │ `ser executado`            │                                              │
   ├────────────────────────────┼──────────────────────────────────────────────┤
   │ `Classe de formato de`     │ galastri\core\output\View                    │
   │ `saída`                    │                                              │
   ├────────────────────────────┼──────────────────────────────────────────────┤
   │ `Template HTML`            │ app/templates/template.php                   │
   ├────────────────────────────┼──────────────────────────────────────────────┤
   │ `View HTML`                │ app/views/Index/main.php                     │
   └────────────────────────────┴──────────────────────────────────────────────┘

💡
┌───────────────────────────────────────────────────────────────────────────┐
│ Note que o método `main()` do exemplo retorna um array vazio, o que é     │
│ permitido. Neste caso específico, nenhum dado adicional é utilizado,      │
│ portanto, a página é montada normalmente pois nem o template, nem a view, │
│ solicitam dados do retorno.                                               │
└───────────────────────────────────────────────────────────────────────────┘
>



--------------------------------------------------------------------------------
▶️ PRÓXIMO TÓPICO:
LINK: 



---

👾 Esta documentação está sendo gerada com auxílio de IA...
🙋‍♂️ ...mas revisada sempre por um humano!
