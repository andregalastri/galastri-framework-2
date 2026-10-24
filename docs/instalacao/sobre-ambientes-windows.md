```md
GALASTRI FRAMEWORK > INSTALAÇÃO
--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                            SOBRE AMBIENTES WINDOWS                           ║
╚══════════════════════════════════════════════════════════════════════════════╝

O Galastri Framework é desenvolvido para servidores Linux. É possível usá-lo em
ambientes Windows, mas pode ocorrer alguma incompatibilidade inesperada.

Se você usa Windows, a minha sugestão é utilizar o WSL (Subsistema do Windows
para Linux) para criar um ambiente Linux dentro do próprio Windows, sem precisar
substituir seu sistema, criar uma partição nova ou configurar manualmente uma
máquina virtual para Linux.

Você continua usando normalmente os programas do Windows, como editor de código
e navegadores, enquanto o WSL com Linux roda o ambiente do servidor web com o
framework.

⚠️
┌──────────────────────────────────────────────────────────────────────────────┐
│ Se você já utiliza Linux no seu computador ou possui um servidor ou máquina  │
│ virtual com Linux já instalado, você não precisa seguir este tutorial.       │
│                                                                              │
│ Vá direto para a seção seguinte:                                             │
│ LINK: /docs/instalacao/preparando-o-ambiente-de-desenvolvimento.md           │
└──────────────────────────────────────────────────────────────────────────────┘
>



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                                PRÉ-REQUISITOS                                ║
╚══════════════════════════════════════════════════════════════════════════════╝

Para acompanhar este tutorial, seu PC precisa ter:

*  Windows 11 ou Windows 10 versão `2004`, compilação `19041` ou posterior.

*  Acesso de administrador no Windows.

*  Conexão com a internet para baixar os componentes.

*  Virtualização do processador habilitada na BIOS/UEFI da placa-mãe.

*  Recurso `Plataforma de Máquina Virtual` instalado no Windows.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                         INSTALANDO O WSL E O UBUNTU                          ║
╚══════════════════════════════════════════════════════════════════════════════╝

Vamos usar o Ubuntu que é a distribuição Linux mais simples de configurar.

1. Abra o `PowerShell` como Administrador.

2. Na janela que abrir, execute o seguinte comando:

   💻
   ┌─────────────────────────┐
   │ wsl --install -d Ubuntu │
   └─────────────────────────┘
   ``
   
   Esse comando instala o WSL e o Ubuntu de uma vez. Em uma instalação nova, o
   WSL versão 2 será instalado por padrão.
   
3. Após a conclusão da instalação, reinicie o computador.

4. Procure por `Ubuntu` no menu Iniciar e abra-o. Nesta primeira execução, ele
   pode levar alguns instantes para terminar de preparar os arquivos.
   
   ⚠️
   ┌───────────────────────────────────────────────────────────────────────────┐
   │ Se o Ubuntu ainda não estiver disponível, repita o comando de instalação  │
   │ no PowerShell como administrador.                                         │
   └───────────────────────────────────────────────────────────────────────────┘
   >


#                         CRIANDO SEU USUÁRIO NO UBUNTU                        <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. Na primeira execução, o Ubuntu solicitará que você crie um nome de usuário e
   uma senha.

   Esse usuário é independente do seu usuário no Windows e a senha será
   utilizada para autorizar tarefas administrativas dentro do Linux.

   Para fins didáticos, vamos usar o nome de usuário `desenvolvedor` nesta
   documentação, mas você pode usar o que preferir.

   💡
   ┌───────────────────────────────────────────────────────────────────────────┐
   │ Ao digitar a senha, nada aparecerá na tela. Esse é o comportamento normal │
   │ do terminal Linux. As teclas estão sendo recebidas, mesmo sem aparecer    │
   │ nenhum caractere. Digite a senha, pressione `Enter` e repita quando for   │
   │ solicitado.                                                               │
   └───────────────────────────────────────────────────────────────────────────┘
   >

2. Quando terminar, aparecerá uma linha parecida com esta:

   🧾
   ┌───────────────────────────────────────────────────────────────────────────┐
   │ desenvolvedor@DESKTOP-123ABC:~$                                           │
   └───────────────────────────────────────────────────────────────────────────┘
   @
   
   Essa linha indica que o terminal está pronto para receber comandos.


#                  CONFERINDO SE ESTAMOS USANDO O WSL VERSÃO 2                 <
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. Abra uma janela do `PowerShell` e execute:

   💻
   ┌──────────────────────┐
   │ wsl --list --verbose │
   └──────────────────────┘
   ``

2. O resultado deverá conter uma linha semelhante a esta:

   🧾
   ┌───────────────────────────────────────────────────────────────────────────┐
   │ NAME      STATE           VERSION                                         │
   │ * Ubuntu    Running         2                                             │
   └───────────────────────────────────────────────────────────────────────────┘
   @
   
   Confira se a coluna VERSION mostra `2` na linha do `Ubuntu`. Essa coluna
   informa a versão do WSL utilizada, e não a versão do Ubuntu.

   Se a coluna VERSION mostrar `1`, execute o comando abaixo para fazer a troca
   para a versão `2` no PowerShell:
   
   💻
   ┌────────────────────────────┐
   │ wsl --set-version Ubuntu 2 │
   └────────────────────────────┘
   ``

   Aguarde a conversão e quando acabar, confira se ela foi aplicada rodando
   novamente o comando abaixo:

   💻
   ┌──────────────────────┐
   │ wsl --list --verbose │
   └──────────────────────┘
   ``



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                             ATUALIZANDO O UBUNTU                             ║
╚══════════════════════════════════════════════════════════════════════════════╝

*  Volte para o terminal do Ubuntu para atualizarmos os programas do sistema
   Linux.

   Execute os dois comandos abaixo, um por vez:

   💻
   ┌──────────────────┐
   │ sudo apt update  │
   │ sudo apt upgrade │
   └──────────────────┘
   ``

   Se for pedido uma senha, informe a que você criou anteriormente.
   Se aparecer uma pergunta de confirmação, responda `s` ou `y` e prossiga.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                 ACESSANDO OS ARQUIVOS DO UBUNTU PELO WINDOWS                 ║
╚══════════════════════════════════════════════════════════════════════════════╝

1. No terminal do Ubuntu, execute os comandos abaixo, um por vez:

   💻
   ┌────────────────┐
   │ cd ~           │
   │ explorer.exe . │ ⚠️ o ponto . faz parte do comando
   └────────────────┘
   ``

2. Sua pasta do Ubuntu será aberta direto no Explorer, permitindo que você
   acesse os arquivos do Linux diretamente pelo Windows.

   ℹ️
   ┌───────────────────────────────────────────────────────────────────────────┐
   │ Vamos manter os arquivos do projeto dentro dessa pasta.                   │
   │                                                                           │
   │ Isso costuma oferecer melhor desempenho do que trabalhar em pastas do     │
   │ Windows, como `C:\Users\...`.                                             │
   └───────────────────────────────────────────────────────────────────────────┘
   >



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                            RESOLUÇÃO DE PROBLEMAS                            ║
╚══════════════════════════════════════════════════════════════════════════════╝

┌─────────────────────────┬────────────────────────────────────────────────────┐
│ _PROBLEMA_              │ _POSSÍVEL SOLUÇÃO_                                 │
├─────────────────────────┼────────────────────────────────────────────────────┤
│ Instalação parada em 0% │ Interrompa o comando com Ctrl + C e tente          │
│ durante o download      │ novamente com este outro código `PowerShell`       │
│                         │ como administrador:                                │
│                         │                                                    │
│                         │ ┌────────────────────────────────────────┐         │
│                         │ │ wsl --install --web-download -d Ubuntu │         │
│                         │ └────────────────────────────────────────┘         │
│                         │ ``                                                 │
│                         │                                                    │
│                         │ Este comando utiliza uma forma alternativa de      │
│                         │ download.                                          │
├─────────────────────────┼────────────────────────────────────────────────────┤
│ Apareceu um erro sobre  │ Confira se a virtualização está habilitada na      │
│ virtualização           │ BIOS/UEFI da placa-mãe. Se não estiver, é          │
│                         │ necessário habilitar.                              │
│                         │                                                    │
│                         ├────────────────────────────────────────────────────┤
│                         │ Confira se o recurso Plataforma de Máquina Virtual │
│                         │ está ativado nos recursos do Windows:              │
│                         │                                                    │
│                         │ 1. Abra o menu Iniciar.                            │
│                         │                                                    │
│                         │ 2. Procure por Ativar ou desativar recursos do     │
│                         │ Windows.                                           │
│                         │                                                    │
│                         │ 3. Confira se a opção Plataforma de Máquina        │
│                         │ Virtual está marcada. Se não estiver, marque-a     │
│                         │ e prossiga para a instalação.                      │
│                         │                                                    │
│                         │ 4. Reinicie o computador assim que a instalação    │
│                         │ terminar.                                          │
└─────────────────────────┴────────────────────────────────────────────────────┘



--------------------------------------------------------------------------------
▶️ PRÓXIMO TÓPICO:
LINK: /docs/instalacao/preparando-o-ambiente-de-desenvolvimento.md
