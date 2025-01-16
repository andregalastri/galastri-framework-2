![Logo do Galastri Framework](https://user-images.githubusercontent.com/49572917/112453870-df5b8700-8d36-11eb-9c31-0c3a628e5178.png)

# Galastri Framework
[![Licença MIT](https://img.shields.io/badge/Licença-MIT-yellow.svg)](https://github.com/andregalastri/galastri-framework/blob/master/galastri/LICENSE) [![Static Badge](https://img.shields.io/badge/Documentação-Wiki-09f)](https://github.com/andregalastri/galastri-framework-2/wiki)


## Sobre
Este é um simple microframework PHP 8 criado como um estudo de caso para desenvolver webapps e websites.

O framework inicialmente era uma forma de estudar a linguagem PHP. Ao longo do desenvolvimento, o framework foi se mostrando viável para o desenvolvimento de sistemas web, APIs e sites em geral.

Atualmente o framework visa ser uma maneira mais simples de se iniciar em programação PHP ao mesmo tempo que se mostra uma boa alternativa para projetos reais.

**Aviso**<br>
Este é um projeto em desenvolvimento inicial. Isso significa que posso mudar drasticamente a forma como o framework funciona entre cada commit e sem qualquer aviso. Se você usar o framework em projetos reais, é uma boa ideia mantê-lo na versão que você está usando, sem atualizá-lo. Se você quiser atualizar para versões mais recentes, faça um backup antes e esteja ciente das mudanças feitas.


## Recursos
- Classe **`Database`** para banco de Dados MySQL, PostgreSQL e SQLite de fácil configuração e uso;
- Controle de **roteamento de URL** fácil com múltiplos parâmetros e configurações;
- Forma fácil de retornar dados apenas configurando qual *output* será usado (HTML, JSON ou Arquivo);
- Classe **`Redirect`** para redirecionamentos simplificados
- Classe **`Fetch`** que resolve os problemas de compatibilidade com as variáveis globais $_POST ou $_GET ao usar chamadas *Javascript Promise*;
- Classe **`Authentication`** para proteção de rotas;
- Classe **`Permission`** para controle de permissões;
- Poderosas classes de **tipo**, com múltiplos métodos para formatar, validar e armazenar dados;
