<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                     ARQUIVO /app/views/Index/main.php                     ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * No template, esta é a view da página inicial, que é incluída via
 * `$g->include('view')` no `<body>`.
 * 
 * A view recebe o mesmo objeto `$g` usado no template, que, neste exemplo, nos
 * permite escrever a versão no HTML.
 * ─────────────────────────────────────────────────────────────────────────────
 */
?>

<b>GALASTRI FRAMEWORK</b>
<br>

<small>
    <?php
        /**
         * Aqui, `print()` busca 'galastriVersion' dentro de 'meta' e escreve o
         * valor diretamente no HTML, para exibir a versão.
         */
        
        $g->print('meta', 'galastriVersion');
    ?>
</small>

<br>
<br>
<br>

<a href="https://framework.andregalastri.com.br" target="_blank">DOCUMENTAÇÃO</a>
<br>

<a href="https://github.com/andregalastri/galastri-framework-2" target="_blank">GITHUB</a>



<?php
/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ◀️ TÓPICO ANTERIOR:
 * 🔗 LINK: /galastri/core/output/View.php
 */
