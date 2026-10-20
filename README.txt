╔══════════════════════════════════════════════════════════════════════════════╗
║                              GALASTRI FRAMEWORK                              ║
╚══════════════════════════════════════════════════════════════════════════════╝

📁 Documentação:
https://github.com/andregalastri/galastri-framework-2/galastri/docs/galastri-framework.txt


O Galastri Framework é um microframework PHP 8 que comecei para desenvolver
sites, sistemas web e APIs. Ele reúne recursos para organizar e simplificar o
código das aplicações web e processar requisições.

O projeto nasceu como uma forma de eu estudar a linguagem PHP e, ao longo do
desenvolvimento, foi evoluindo para atender aplicações reais. Minha proposta não
é concorrer nem criar um substituto aos grandes frameworks PHP, mas sim oferecer
uma base simples de usar e compreender que seja acessível também para quem está
começando a programar em PHP.  


⚠️ IMPORTANTE!
┌──────────────────────────────────────────────────────────────────────────────┐
│ O framework está em fase alpha. Seu funcionamento pode mudar entre commits,  │
│ sem aviso prévio, e essas mudanças podem exigir ajustes nas aplicações que o │
│ utilizam.                                                                    │
└──────────────────────────────────────────────────────────────────────────────┘


--------------------------------------------------------------------------------



╔══════════════════════════════════════════════════════════════════════════════╗
║                                   RECURSOS                                   ║
╚══════════════════════════════════════════════════════════════════════════════╝

✓ Roteamento De Urls
│ ──────────────────  
│ Organização das rotas em nós e endpoints, com suporte a parâmetros, nós
│ dinâmicos e configurações por contexto!
    
✓ Formatos De Saída
│ ─────────────────  
│ Respostas em HTML, JSON e arquivos previsíveis e fáceis de configurar!
    
✓ Banco De Dados
│ ──────────────  
│ A classe `Database` permite acesso a bancos MySQL e SQLite com múltiplos
│ recursos que facilitam consultas e operações!
    
✓ Obtenção De Dados De Requisições
│ ────────────────────────────────
│ A classe `HttpData` permite consultar dados recebidos pela URL, por
│ formulários ou via JSON de forma centralizada, sem a necessidade de uso de
│ múltiplas funções!
    
✓ Autenticação
│ ────────────  
│ A classe `Authentication` já vem pronta para criar, verificar e encerrar
│ autenticações baseadas em sessões e cookies!
    
✓ Permissões
│ ──────────
│  A classe `Permission` possui o boilerplate preparado para verificar as
│  permissões necessárias de acesso à recursos ou para executar operações!
    
✓ Validação De Dados
│ ──────────────────  
│ A classe `Validation` é rica em ferramentas para verificar se textos, números,
│ datas e horários atendem às regras definidas pela aplicação!
    
✓ Envio De Dados Por Stream
│ ─────────────────────────
│ A classe `ResponseStream` facilita o envio de respostas ao frontend, como
│ mensagens ou resultados parciais, em formato JSON, antes do término da
│ requisição!
    
✓ Tratamento De Erros  
│ ───────────────────
│ Múltiplos recursos para registrar falhas e controlar a apresentação das
│ informações de erro conforme as configurações da aplicação.  
