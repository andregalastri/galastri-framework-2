```md
GALASTRI FRAMEWORK > INSTALAÇÃO
--------------------------------------------------------------------------------

⚠️
┌──────────────────────────────────────────────────────────────────────────────┐
│ _ATENÇÃO, USUÁRIOS WINDOWS_                                                  │
│ Este tutorial é voltado para ambientes Linux. Antes de prosseguir, veja      │
│ primeiro o passo a passo abaixo para montar um ambiente Linux dentro do      │
│ Windows com WSL.                                                             │
│                                                                              │
│ LINK: /docs/instalacao/sobre-ambientes-windows.md                            │
└──────────────────────────────────────────────────────────────────────────────┘
>



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                   PREPARANDO O AMBIENTE DE DESENVOLVIMENTO                   ║
╚══════════════════════════════════════════════════════════════════════════════╝

Vamos instalar e configurar os programas necessários. Abaixo uma breve descrição
de cada um:

_📦 Apache:_
É o servidor que recebe as requisições do navegador e entrega as respostas da
aplicação.

_📦 PHP-FPM:_
É o programa que executa o código PHP em conjunto com o Apache.

_📦 Git:_
É por onde vamos baixar o projeto e acompanhar as alterações no código.

_📦 Composer:_
É o gerenciador de dependências do PHP que permite o carregamento das classes do
projeto além de bibliotecas que a aplicação precisar.

⚠️
┌──────────────────────────────────────────────────────────────────────────────┐
│ _ATENÇÃO, USUÁRIOS WINDOWS_                                                  │
│ No Windows com WSL, lembre-se de executar os comandos desta seção no         │
│ terminal do Ubuntu, não no CMD ou Powershell.                                │
│                                                                              │
│ Os passos consideram uma instalação nova do Ubuntu. Se você já possui um     │
│ servidor configurado com outros sites e tal, vai precisar fazer adaptações.  │
└──────────────────────────────────────────────────────────────────────────────┘
>



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                             INSTALANDO O APACHE                              ║
╚══════════════════════════════════════════════════════════════════════════════╝

*  Execute os comandos abaixo, um por vez:

   💻
   ┌──────────────────────────┐
   │ sudo apt update          │
   │ sudo apt install apache2 │
   └──────────────────────────┘
   ``

   Se aparecer uma pergunta para continuar, confirme.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                      INSTALANDO O PHP E SUAS EXTENSÕES                       ║
╚══════════════════════════════════════════════════════════════════════════════╝

1. Execute os comandos abaixo, um por vez:

   💻
   ┌───────────────────────────────────────────────────────────────────────┐
   │ sudo apt install php-fpm php-cli php-gd php-mbstring php-xml php-curl │
   │ sudo apt install php-zip php-mysql php-sqlite3                        │
   └───────────────────────────────────────────────────────────────────────┘
   ``

   Esses comandos instalam o PHP e as extensões que o framework vai usar.

2. Ao final, confira se a versão instalada é `8.0` ou superior:

   💻
   ┌────────┐
   │ php -v │
   └────────┘
   ``



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                  CONFIGURANDO O APACHE PARA USAR O PHP-FPM                   ║
╚══════════════════════════════════════════════════════════════════════════════╝

1. Execute o comando abaixo:

   💻
   ┌──────────────────────────────────────────┐
   │ sudo a2enmod proxy_fcgi setenvif rewrite │
   └──────────────────────────────────────────┘
   ``

   Este comando habilita os módulos do Apache que são necessários para a
   execução do PHP-FPM e framework.

2. Agora execute, um por vez:

   💻
   ┌─────────────────────────────────────────────────────────────────┐
   │ PHP_V=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;') │
   │                                                                 │
   │ sudo a2enconf "php${PHP_V}-fpm"                                 │
   │ sudo systemctl enable --now "php${PHP_V}-fpm"                   │
   │ sudo systemctl enable --now apache2                             │
   └─────────────────────────────────────────────────────────────────┘
   ``

   Isso habilita a configuração do PHP-FPM no Apache e ativa os serviços de
   ambos os programas para que iniciem junto com o Ubuntu sempre que você
   iniciar o ambiente.

3. Por fim, execute:

   💻
   ┌────────────────────────────────┐
   │ sudo systemctl restart apache2 │
   └────────────────────────────────┘
   ``
   
   Isso reinicia o Apache para aplicar as configurações.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                        INSTALANDO O GIT E O COMPOSER                         ║
╚══════════════════════════════════════════════════════════════════════════════╝

*  Execute o comando abaixo:

   💻
   ┌───────────────────────────────────────────────┐
   │ sudo apt install git composer curl unzip nano │
   └───────────────────────────────────────────────┘
   ``
  
   Isso instala o Git, o Composer e ferramentas auxiliares para baixar arquivos,
   descompactá-los e editar configurações pelo terminal.


#                    CONFIGURANDO SUA IDENTIFICAÇÃO NO GIT                     <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Usaremos o Git apenas para baixar e atualizar o framework quando necessário, mas
se você pretende contribuir com o desenvolvimento do framework em si e fazer
commits, é necessário registrar um nome e um e-mail.

1. Execute os comandos abaixo, um por vez, substituindo os exemplos pelos seus
   dados:

   💻
   ┌──────────────────────────────────────────────────────────┐
   │ git config --global user.name "Seu nome/nome_de_usuario" │
   │ git config --global user.email "seu-email@exemplo.com"   │
   └──────────────────────────────────────────────────────────┘
   ``

2. Depois confira se os valores foram aplicados:

   💻
   ┌────────────────────────────────┐
   │ git config --global user.name  │
   │ git config --global user.email │
   └────────────────────────────────┘
   ``
   
⚠️
┌──────────────────────────────────────────────────────────────────────────────┐
│ Essa identificação será utilizada nos commits, mas _não faz login_ na sua    │
│ conta do GitHub.                                                             │
│                                                                              │
│ Caso vá usar o Git pra subir os commits ao Github também, é melhor informar  │
│ seu nome de usuário e e-mail cadastrado na plataforma.                       │
└──────────────────────────────────────────────────────────────────────────────┘
>



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                      CONFERINDO O APACHE PELO NAVEGADOR                      ║
╚══════════════════════════════════════════════════════════════════════════════╝

Com o Apache funcionando, precisamos confirmar se ele está conseguindo executar
arquivos `.php`.

Vamos criar um arquivo temporário chamado `teste.php` na pasta padrão do Apache,
só pra ver se a integração está funcionando.

1. Execute o comando abaixo:

   💻
   ┌───────────────────────────────────┐
   │ sudo nano /var/www/html/teste.php │
   └───────────────────────────────────┘
   ``

2. Copie e cole o conteúdo abaixo:

   💻
   ┌────────────────────────────────────────────────────┐
   │ <?php                                              │
   │                                                    │
   │ header('Content-Type: text/plain; charset=utf-8'); │
   │                                                    │
   │ echo "Apache e PHP funcionando!\n";                │
   │ echo "Versão do PHP: " . PHP_VERSION . "\n";       │
   │ echo "Modo de execução: " . PHP_SAPI . "\n";       │
   └────────────────────────────────────────────────────┘
   ``

   Salve o arquivo (Ctrl + O), confirme com (Enter) e saia (Ctrl + X).

3. Agora acesse no navegador o endereço http://localhost/teste.php

   O resultado deverá ser parecido com este:
   
   🧾
   ┌───────────────────────────────────────────────────────────────────────────┐
   │ Apache e PHP funcionando!                                                 │
   │ Versão do PHP: 8.3.6                                                      │
   │ Modo de execução: fpm-fcgi                                                │
   └───────────────────────────────────────────────────────────────────────────┘
   @

4. Estando tudo certo, remova o arquivo temporário:

   💻
   ┌─────────────────────────────────┐
   │ sudo rm /var/www/html/teste.php │
   └─────────────────────────────────┘
   ``



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                            RESOLUÇÃO DE PROBLEMAS                            ║
╚══════════════════════════════════════════════════════════════════════════════╝

┌──────────────────────────┬────────────────────────────────────────────────────────────────────────┐
│ _PROBLEMA_               │ _POSSÍVEL SOLUÇÃO_                                                     │
├──────────────────────────┼────────────────────────────────────────────────────────────────────────┤
│ O navegador não consegue │ 1. No terminal do Ubuntu, execute o comando                            │
│ abrir a página           │    abaixo:                                                             │
│                          │                                                                        │
│                          │    ┌──────────────────────────────────────────┐                        │
│                          │    │ sudo systemctl status apache2 --no-pager │                        │
│                          │    └──────────────────────────────────────────┘                        │
│                          │    ``                                                                  │
│                          │                                                                        │
│                          │    O comando deverá mostrar `active (running)`.                        │
│                          │                                                                        │
│                          │ 2. Caso não, inicie o Apache com o comando                             │
│                          │    abaixo:                                                             │
│                          │                                                                        │
│                          │    ┌──────────────────────────────┐                                    │
│                          │    │ sudo systemctl start apache2 │                                    │
│                          │    └──────────────────────────────┘                                    │
│                          │    ``                                                                  │
│                          │                                                                        │
│                          │    Se este comando falhar, aí será necessário que                      │
│                          │    você investigue a causa (pode ser muita coisa                       │
│                          │    pra eu explicar aqui).                                              │
├──────────────────────────┼────────────────────────────────────────────────────────────────────────┤
│ O arquivo PHP é baixado  │ 1. Reinicie o PHP-FPM e o Apache para garantir que                     │
│ ou aparece como código   │    estejam rodando.                                                    │
│                          │                                                                        │
│                          │ 2. Execute a sequência de comandos abaixo:                             │
│                          │    ┌─────────────────────────────────────────────────────────────────┐ │
│                          │    │ PHP_V=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;') │ │
│                          │    │ sudo a2enconf "php${PHP_V}-fpm"                                 │ │
│                          │    │ sudo systemctl restart "php${PHP_V}-fpm"                        │ │
│                          │    │ sudo systemctl restart apache2                                  │ │
│                          │    └─────────────────────────────────────────────────────────────────┘ │
│                          │    ``                                                                  │
│                          │                                                                        │
│                          │ 3. Acesse novamente o endereço do teste.                               │
├──────────────────────────┼────────────────────────────────────────────────────────────────────────┤
│ O comando informa que o  │ Este problema tende a acontecer mais no Windows                        │
│ sistema não foi iniciado │ com WSL.                                                               │
│ com systemd              │                                                                        │
│                          │ 1. Execute o comando abaixo no terminal do Ubuntu                      │
│                          │    ┌───────────────────────────┐                                       │
│                          │    │  sudo nano /etc/wsl.conf  │                                       │
│                          │    └───────────────────────────┘                                       │
│                          │    ``                                                                  │
│                          │                                                                        │
│                          │ 2. Na seção `[boot]`, acrescente ou ajuste                             │
│                          │    a opção `systemd=true`, conforme abaixo:                            │
│                          │    ┌──────────────┐                                                    │
│                          │    │ [boot]       │                                                    │
│                          │    │ systemd=true │                                                    │
│                          │    └──────────────┘                                                    │
│                          │    ``                                                                  │
│                          │                                                                        │
│                          │    Se a seção `[boot]` já existir, não repita                          │
│                          │    seu cabeçalho. Caso não exista, acrescente                          │
│                          │    o bloco completo mostrado acima.                                    │
│                          │                                                                        │
│                          │    Salve (Ctrl + O), confirme (Enter) e saia (Ctrl + X).               │
│                          │                                                                        │
│                          │ 3. Abra o PowerShell no Windows e execute o                            │
│                          │    comando abaixo:                                                     │
│                          │                                                                        │
│                          │    ┌────────────────────────┐                                          │
│                          │    │ wsl --terminate Ubuntu │                                          │
│                          │    └────────────────────────┘                                          │
│                          │    ``                                                                  │
│                          │                                                                        │
│                          │ 4. Reabra novamente o Ubuntu e repita os comandos                      │
│                          │    de ativação dos serviços:                                           │
│                          │                                                                        │
│                          │    ┌─────────────────────────────────────────────────────────────────┐ │
│                          │    │ PHP_V=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;') │ │
│                          │    │ sudo a2enconf "php${PHP_V}-fpm"                                 │ │
│                          │    │ sudo systemctl restart "php${PHP_V}-fpm"                        │ │
│                          │    │ sudo systemctl restart apache2                                  │ │
│                          │    └─────────────────────────────────────────────────────────────────┘ │
│                          │    ``                                                                  │
└──────────────────────────┴────────────────────────────────────────────────────────────────────────┘



--------------------------------------------------------------------------------
▶️ PRÓXIMO TÓPICO:
LINK: /docs/instalacao/instalando-o-galastri-framework.md
