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
#                     PREPARANDO AS PERMISSÕES DAS PASTAS                      ║
╚══════════════════════════════════════════════════════════════════════════════╝

O Apache e o PHP-FPM precisam acessar o projeto dentro da sua pasta pessoal.
Também precisamos permitir que o PHP-FPM grave os registros de erro em `logs`.

1. Instale a ferramenta para conceder acesso a um usuário específico:

   💻
   ┌──────────────────────┐
   │ sudo apt install acl │
   └──────────────────────┘
   ``

2. Permita que o usuário `www-data`, usado pelo Apache e pelo PHP-FPM no Ubuntu,
   atravesse sua pasta pessoal para chegar ao projeto:

   💻
   ┌────────────────────────────────────────────────────┐
   │ sudo setfacl -m u:www-data:--x /home/desenvolvedor │
   └────────────────────────────────────────────────────┘
   ``

   Essa permissão permite atravessar a pasta, sem dar permissão para listar seu
   conteúdo. As pastas e os arquivos do projeto devem continuar legíveis pelo
   servidor, como ocorre nas permissões padrão de uma clonagem.

3. Prepare a escrita na pasta de logs:

   💻
   ┌─────────────────────────────────────────────┐
   │ cd /home/desenvolvedor/galastri-framework-2 │
   │ sudo chgrp -R www-data logs                 │
   │ sudo chmod -R g+rwX logs                    │
   │ sudo chmod 2770 logs                        │
   └─────────────────────────────────────────────┘
   ``

   O grupo `www-data` poderá gravar nessa pasta. O bit `2` mantém esse grupo nos
   novos arquivos criados dentro dela.

   Ajuste `desenvolvedor` nos comandos se o seu usuário tiver outro nome.



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
   │     ServerName localhost                                                  │
   │                                                                           │
   │     DocumentRoot /home/desenvolvedor/galastri-framework-2/public          │
   │     DirectoryIndex index.php                                              │
   │                                                                           │
   │     <Directory /home/desenvolvedor/galastri-framework-2/public>           │
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
│ *  Mantenha o `DocumentRoot` apontando para a pasta `public`, evite          │
│    apontar para outras pastas do projeto para evitar vazamento de arquivos e │
│    código fonte pelo navegador.                                              │
│                                                                              │
│ *  O `AllowOverride All` permite utilizar as regras do arquivo `.htaccess`   │
│    que acompanha o projeto.                                                  │
│                                                                              │
│ *  O `flushpackets=on` permite que o Apache encaminhe os dados parciais      │
│    enviados pelo PHP-FPM durante o processamento, sendo útil para os         │
│    recursos de streaming do framework.                                       │
└──────────────────────────────────────────────────────────────────────────────┘
>



--------------------------------------------------------------------------------
╔══════════════════════════════════════════════════════════════════════════════╗
#                            ATIVANDO A CONFIGURAÇÃO                           ║
╚══════════════════════════════════════════════════════════════════════════════╝

1. No terminal, execute o comando abaixo:

   💻
   ┌─────────────────────────────────┐
   │ sudo a2dissite 000-default.conf │
   │ sudo a2ensite galastri.conf     │
   └─────────────────────────────────┘
   ``

   Isso desativa o site padrão e ativa a configuração do framework para atender
   pelo endereço `localhost`.

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
