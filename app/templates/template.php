<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                    ARQUIVO /app/templates/template.php                    ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Arquivo de exemplo para conhecer como usamos o objeto `$g` dentro de um
 * template.
 *
 * Este arquivo define a estrutura HTML da página de exemplo, seus estilos e
 * suas interações. O título vem dos dados preparados pelo controller, e o
 * conteúdo da view é incluído dentro do `<body>`, no ponto em que chamamos
 * `$g->include('view')`.
 *
 * Podemos usar essa mesma estrutura para diferentes views, mantendo no template
 * as partes que elas compartilham.
 * ─────────────────────────────────────────────────────────────────────────────
 */
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>
        <?php
            /**
             * O título da página vem dos dados disponíveis em `$g`. Consultamos a
             * chave 'meta' e, dentro do array que ela armazena, buscamos a chave
             * 'title'.
             *
             * `get()` retorna esse valor, `echo` escreve no HTML.
             */
            
            echo $g->get('meta', 'title');
        ?>
    </title>
</head>

<body style="font-family: monospace;">
    

    <?php
        /**
         * É neste ponto que o conteúdo da view entra na página. O argumento
         * 'view' faz `PhpEngine` usar o caminho do arquivo que a classe `View`
         * já localizou para esta rota.
         *
         * Durante sua execução, a view também pode usar `$g` para consultar os
         * dados do controller. O HTML que ela produzir será inserido aqui e,
         * quando ela terminar, a execução continuará pelo restante do template.
         */
        
        $g->include('view');
    ?>
</body>
</html>



<?php
/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ▶️ PRÓXIMO TÓPICO:
 * 🔗 LINK: /app/views/Index/main.php
 */
