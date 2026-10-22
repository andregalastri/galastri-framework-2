```md
GALASTRI FRAMEWORK > INSTALAÇÃO
--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                       FINALIZANDO A CONFIGURAÇÃO LOCAL                       ║
╚══════════════════════════════════════════════════════════════════════════════╝

Com tudo preparado, agora precisamos apontar o Apache para a pasta `public` do
framework.

Esta pasta fica localizada em `/home/desenvolvedor/galastri-framework-2/public`
e é a porta de entrada do projeto.



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                        CONFIGURANDO O SITE NO APACHE                         ║
╚══════════════════════════════════════════════════════════════════════════════╝

1. Execute o comando no terminal:

   💻
   ┌──────────────────────────────────────────────┐
   │ sudo a2enmod ssl rewrite proxy_fcgi setenvif │
   └──────────────────────────────────────────────┘
   ``

   Isso habilita os módulos necessários.

2. Vamos criar um arquivo de configuração do Apache:

   💻
   ┌──────────────────────────────────────────────────────┐
   │ sudo nano /etc/apache2/sites-available/galastri.conf │
   └──────────────────────────────────────────────────────┘
   ``

3. Copie e cole o conteúdo do arquivo a seguir:

   💻
   ┌───────────────────────────────────────────────────────────────────────────┐
   │ <VirtualHost *:80>                                                        │
   │                                                                           │
   │     DocumentRoot /var/www/galastri-framework-2/public                     │
   │     DirectoryIndex index.php                                              │
   │                                                                           │
   │     <Directory /var/www/galastri-framework-2/public>                      │
   │         Options -Indexes +FollowSymLinks                                  │
   │         AllowOverride All                                                 │
   │         Require all granted                                               │
   │     </Directory>                                                          │
   │                                                                           │
   │     <Proxy "fcgi://localhost/">                                           │
   │         ProxySet flushpackets=on                                          │
   │     </Proxy>                                                              │
   │                                                                           │
   │     ErrorLog ${APACHE_LOG_DIR}/galastri-error.log                         │
   │     CustomLog ${APACHE_LOG_DIR}/galastri-access.log combined              │
   │ </VirtualHost>                                                            │
   └───────────────────────────────────────────────────────────────────────────┘
   ``

   Essa configuração define o caminho do projeto, apontando para a pasta
   `public`.

4. Salve (Ctrl + O), confirme (Enter) e saia (Ctrl + X).

⚠️
┌──────────────────────────────────────────────────────────────────────────────┐
│ * Mantenha o `DocumentRoot` apontando para a pasta `public`, evite           │
│ apontar para outras pastas do projeto para evitar vazamento de arquivos e    │
│ código fonte pelo navegador.                                                 │
│                                                                              │
│ * O `AllowOverride All` permite utilizar as regras do arquivo `.htaccess`    │
│ que acompanha o projeto.                                                     │
│                                                                              │
│ * O `flushpackets=on` permite que o Apache encaminhe os dados parciais       │
│ enviados pelo PHP-FPM durante o processamento, sendo útil para os recursos   │
│ de streaming do framework.                                                   │
└──────────────────────────────────────────────────────────────────────────────┘
>



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                            ATIVANDO A CONFIGURAÇÃO                           ║
╚══════════════════════════════════════════════════════════════════════════════╝

1. No terminal, execute o comando abaixo:

   💻
   ┌─────────────────────────────┐
   │ sudo a2ensite galastri.conf │
   └─────────────────────────────┘
   ``

   Isso vai ativar o arquivo de configuração que criamos

2. Teste se a configuração possui algum erro de configuração:

   💻
   ┌────────────────────────────┐
   │ sudo apache2ctl configtest │
   └────────────────────────────┘
   ``

   O resultado deste teste deve terminar com:

   🧾
   ┌───────────────────────────────────────────────────────────────────────────┐
   │ Syntax OK                                                                 │
   └───────────────────────────────────────────────────────────────────────────┘
   @

   Se aparecer um erro, será necessário investigar e corrigir o problema
   indicado antes de reiniciar o Apache.

3. Reinicie o Apache:

   💻
   ┌────────────────────────────────┐
   │ sudo systemctl restart apache2 │
   └────────────────────────────────┘
   ``



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                             ACESSANDO O FRAMEWORK                            ║
╚══════════════════════════════════════════════════════════════════════════════╝

*  Abra o navegador e acesse: http://localhost

   Você deverá ver a página inicial do framework, o que indicará que o ambiente
   está pronto!



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                            RESOLUÇÃO DE PROBLEMAS                            ║
╚══════════════════════════════════════════════════════════════════════════════╝

┌─────────────────────────┬────────────────────────────────────────────────────┐
│ _PROBLEMA_              │ _POSSÍVEL SOLUÇÃO_                                 │
├─────────────────────────┼────────────────────────────────────────────────────┤
│ Aparece a página padrão │ Confira se você ativou o site executando o         │
│ do Apache               │ comando:                                           │
│                         │                                                    │
│                         │ ┌────────────────────────────────┐                 │
│                         │ │ sudo a2ensite galastri.conf    │                 │
│                         │ │ sudo systemctl restart apache2 │                 │
│                         │ └────────────────────────────────┘                 │
│                         │ ``                                                 │
│                         │                                                    │
├─────────────────────────┼────────────────────────────────────────────────────┤
│ Aparece erro 500 ou uma │ Você vai precisar rever a configuração como um     │
│ página em branco        │ todo.                                              │
│                         │                                                    │
│                         │ 1. Consulte o registro do Apache e verifique o que │
│                         │    os erros estão dizendo:                         │
│                         │                                                    │
│                         │    ┌───────────────────────────────────────┐       │
│                         │    │ sudo tail -n 50 \                     │       │
│                         │    │   /var/log/apache2/galastri-error.log │       │
│                         │    └───────────────────────────────────────┘       │
│                         │    ``                                              │
│                         │                                                    │
│                         │ 2. Confira também as permissões das pastas do      │
│                         │    framework se executou `composer install` no     │
│                         │    projeto.                                        │
│                         │                                                    │
├─────────────────────────┼────────────────────────────────────────────────────┤
│ O Apache não inicia     │ Você vai precisar rever a configuração do Apache.  │
│ depois da configuração  │                                                    │
│                         │ Confira o que o teste responde ao executar o       │
│                         │ comando abaixo:                                    │
│                         │                                                    │
│                         │ ┌────────────────────────────┐                     │
│                         │ │ sudo apache2ctl configtest │                     │
│                         │ └────────────────────────────┘                     │
│                         │ ``                                                 │
│                         │                                                    │
└─────────────────────────┴────────────────────────────────────────────────────┘



--------------------------------------------------------------------------------
▶️ PRÓXIMO TÓPICO: Estrutura e funcionamento

LINK: /docs/estrutura-e-funcionamento/estrutura-e-funcionamento.md



---

👾 Esta documentação está sendo gerada com auxílio de IA...
🙋‍♂️ ...mas revisada sempre por um humano!
