```md
GALASTRI FRAMEWORK > INSTALAÇÃO
--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                       INSTALANDO O GALASTRI FRAMEWORK                        ║
╚══════════════════════════════════════════════════════════════════════════════╝

Com o ambiente preparado, vamos baixar o Galastri Framework e preparar seus
arquivos com o Composer!

Vamos guardar o framework em `/home/desenvolvedor/galastri-framework-2`.

💡
┌──────────────────────────────────────────────────────────────────────────────┐
│ Para fins didáticos, vamos usar o nome de usuário `desenvolvedor` nesta      │
│ documentação. Ajuste os códigos para o seu usuário, caso o nome seja outro.  │
└──────────────────────────────────────────────────────────────────────────────┘
>

1. Execute os comandos abaixo, um por vez:

   💻
   ┌─────────────────────────────────────────────────────────────────────┐
   │ cd ~                                                                │
   │ git clone https://github.com/andregalastri/galastri-framework-2.git │
   └─────────────────────────────────────────────────────────────────────┘
   ``

   O projeto será baixado na pasta pessoal do seu usuário.
   
2. Quando o download finalizar, confira os arquivos:

   💻
   ┌──────────────────────────┐
   │ ls galastri-framework-2/ │
   └──────────────────────────┘
   ``

   O comando deverá encontrar a estrutura de pastas do framework, conforme
   abaixo:

   📂
   ┌───────────────────────────────────────────────────────────────────────────┐
   │ app                                                                       │
   │ docs                                                                      │
   │ galastri                                                                  │
   │ logs                                                                      │
   │ misc                                                                      │
   │ public                                                                    │
   │ vendor                                                                    │
   │ composer.json                                                             │
   │ composer.lock                                                             │
   │ LICENSE                                                                   │
   │ README.md                                                                 │
   └───────────────────────────────────────────────────────────────────────────┘
   @



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                          PREPARANDO AS DEPENDÊNCIAS                          ║
╚══════════════════════════════════════════════════════════════════════════════╝

1. Entre na pasta do projeto e prepare as dependências:

   💻
   ┌───────────────────────────┐
   │ cd ~/galastri-framework-2 │
   │ composer install          │
   └───────────────────────────┘
   ``

   O Composer utiliza as configurações do projeto para preparar as dependências
   e gerar os arquivos responsáveis pelo carregamento das classes.

   ℹ️
   ┌───────────────────────────────────────────────────────────────────────────┐
   │ Atualmente, o framework não possui bibliotecas externas para baixar pelo  │
   │ Composer, então é normal aparecer uma mensagem como `Nothing to install`. │
   └───────────────────────────────────────────────────────────────────────────┘
   >
   
2. Confira os requisitos do projeto:

   💻
   ┌──────────────────────────────┐
   │ composer check-platform-reqs │
   └──────────────────────────────┘
   ``

   Esse comando é só uma verificação para confirmar se o PHP e suas extensões
   atendem aos requisitos declarados pelo projeto.

   Os itens verificados devem aparecer com o resultado `success`.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                            RESOLUÇÃO DE PROBLEMAS                            ║
╚══════════════════════════════════════════════════════════════════════════════╝

┌───────────────────────────┬──────────────────────────────────────────────────┐
│ _PROBLEMA_                │ _POSSÍVEL SOLUÇÃO_                               │
├───────────────────────────┼──────────────────────────────────────────────────┤
│ A pasta do projeto já     │ Confira se ela já contém uma instalação do       │
│ existe                    │ framework. Se contiver, entre nela e continue    │
│                           │ pela preparação das dependências.                │
│                           │                                                  │
│                           │ Se pertencer a outro projeto, então escolha      │
│                           │ outra pasta e ajuste os caminhos do tutorial.    │
├───────────────────────────┼──────────────────────────────────────────────────┤
│ O Composer não encontra   │ 1. No terminal, execute o comando para acessar a │
│ o arquivo `composer.json` │    pasta do framework:                           │
│                           │                                                  │
│                           │    ┌────────────────────────────┐                │
│                           │    │ cd ~/galastri-framework-2  │                │
│                           │    └────────────────────────────┘                │
│                           │    ``                                            │
│                           │                                                  │
│                           │ 2. Execute o Composer novamente:                 │
│                           │                                                  │
│                           │   ┌──────────────────┐                           │
│                           │   │ composer install │                           │
│                           │   └──────────────────┘                           │
│                           │   ``                                             │
├───────────────────────────┼──────────────────────────────────────────────────┤
│ O Composer informa que a  │ 1. Execute o comando abaixo:                     │
│ extensão `ext-gd` está    │                                                  │
│ ausente                   │   ┌──────────────────────────┐                   │
│                           │   │ sudo apt install php-gd  │                   │
│                           │   └──────────────────────────┘                   │
│                           │   ``                                             │
│                           │                                                  │
│                           │ 2. Execute o Composer novamente:                 │
│                           │                                                  │
│                           │   ┌───────────────────────────┐                  │
│                           │   │ cd ~/galastri-framework-2 │                  │
│                           │   │ composer install          │                  │
│                           │   └───────────────────────────┘                  │
│                           │   ``                                             │
├───────────────────────────┼──────────────────────────────────────────────────┤
│ Aparece Permission        │ Confira se você está executando os comandos      │
│ denied ao tentar baixar   │ dentro da sua pasta pessoal                      │
│ ou preparar os arquivos   │ `/home/desenvolvedor`, ou se você está logado    │
│                           │ com o usuário desenvolvedor.                     │
│                           │                                                  │
│                           │ Atente-se que os comandos do Git e do            │
│                           │ Composer não usam `sudo`.                        │
│                           │ >                                                │
└───────────────────────────┴──────────────────────────────────────────────────┘



--------------------------------------------------------------------------------
▶️ PRÓXIMO TÓPICO: Finalizando a configuração local
LINK: /docs/instalacao/finalizando-a-configuracao-local.md



---

👾 Esta documentação está sendo gerada com auxílio de IA...
🙋‍♂️ ...mas revisada sempre por um humano!
