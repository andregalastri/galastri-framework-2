<?php

return [
    /**
     * O array começa com uma chave que define qual trecho da URL será tratado
     * como a raiz do gerenciamento de rotas.
     *
     * Basicamente, ela deve responder à pergunta:
     * - Qual parte da URL dá acesso ao `index.php` do framework?
     *
     * Se `index.php` estiver configurado para rodar imediatamente junto ao
     * domínio, então configuramos aqui que a raiz é '/'. Com isso, o framework
     * deve começar o gerenciamento das rotas logo após o domínio.
     *
     * Ou seja, ao acessar `https://exemplo.com/clientes`, por exemplo,
     * '/clientes' já passa a ser tratada como rota pelo framework.
     *
     * Porém, podemos mudar isso se for necessário. Se `index.php` estiver
     * configurado para rodar a partir de '/projeto', então devemos informar
     * esta alteração aqui também, substituindo '/' por '/projeto'.
     *
     * Neste caso, ao acessar `https://exemplo.com/projeto/clientes`, o
     * framework reconhecerá `/projeto` como caminho raiz, não como rota,
     * enquanto '/clientes' passa a ser tratada como rota pelo framework.
     *
     * Se mantivermos '/' neste caso, o framework irá tratar todo o trecho
     * `/projeto/clientes` como rota, tentando encontrar 'projeto' entre as
     * rotas da aplicação, podendo resultar em um erro de rota não encontrada.
     */
    
    '/' => [
        'displayErrors' => true,
        'showTrace' => false,
        'title' => 'Galastri Framework',

        '@main' => [
        ],
    ],
];
