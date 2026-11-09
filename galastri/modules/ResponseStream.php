<?php
/**
 * -----------------------------------------------------------------------------
 * 📂
 * ╔═══════════════════════════════════════════════════════════════════════════╗
 * ║                ARQUIVO /galastri/modules/ResponseStream.php               ║
 * ╚═══════════════════════════════════════════════════════════════════════════╝
 * Este módulo permite que o controller envie atualizações enquanto ainda está
 * processando a requisição.
 * 
 * A cada chamada a `ResponseStream::send()`, o framework envia uma linha de
 * JSON na resposta HTTP. O frontend pode ler essa linha assim que ela chega e
 * atualizar a tela enquanto o controller continua trabalhando.
 * 
 * Quando o controller termina seu processamento, o framework prossegue
 * normalmente, enviando a resposta do controller na mesma resposta HTTP.
 * ─────────────────────────────────────────────────────────────────────────────
 */



/**
 * 🟢 NAMESPACE DA CLASSE
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * `ResponseStream` faz parte dos módulos que o framework oferece pra aplicação.
 */

namespace galastri\modules;



/**
 * 🟢 CLASSES UTILIZADAS
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ┌────────┬──────────────────────────────────────────────────────────────────┐
 * │ Classe │ Uso                                                              │
 * ├────────┼──────────────────────────────────────────────────────────────────┤
 * │ `Json` │ Prepara a resposta em NDJSON e escreve nela os dados de cada     │
 * │        │ chamada a `ResponseStream::send()`                               │
 * └────────┴──────────────────────────────────────────────────────────────────┘
 */

use galastri\core\output\Json;




/**
 * 🟢 CLASSE ResponseStream
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Use `ResponseStream` em rotas configuradas com `output => 'json'` quando
 * quiser enviar dados antes de o controller terminar.
 *
 * Imagine que o controller esteja importando uma planilha. Depois de gravar um
 * lote, ele pode chamar `ResponseStream::send(['importados' => 250])` pra que a
 * tela possa mostrar quantos registros já foram importados enquanto os próximos
 * lotes ainda estão sendo processados.
 *
 * Cada chamada acrescenta uma linha com seu próprio JSON à resposta. Quando o
 * controller termina, o framework envia os dados que ele reuniu para a resposta
 * final na última linha do NDJSON.
 */

final class ResponseStream
{
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                              CONSTANTES                               ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     * 
     * Esta classe não possui constantes.
     */
    
    
    
     /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                             PROPRIEDADES                              ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     * 
     * Esta classe não possui propriedades.
     */
    
    
    
    /**
     * ╔═══════════════════════════════════════════════════════════════════════╗
     * ║                                MÉTODOS                                ║
     * ╚═══════════════════════════════════════════════════════════════════════╝
     */
    
    /**
     * 🟢 CLASSE ESTÁTICA
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Os recursos desta classe são acessados diretamente por chamadas como
     * `ResponseStream::send()`, sem precisar criar um objeto.
     *
     * Por isso, deixamos o construtor privado para impedir que a classe seja
     * instanciada com `new ResponseStream()`.
     */
    
    private function __construct() {}

    
    
    /**
     * 🟢 ENVIANDO UMA ATUALIZAÇÃO
     * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
     * Passa os dados para `Json::stream()`. Na primeira chamada, esse método
     * prepara a resposta para o streaming. Nas chamadas seguintes, ele segue
     * direto para o envio.
     *
     * _________________________________
     * @param array $data  
     *    Dados que o controller quer enviar neste ponto do processamento.
     * _________________________________
     * @return void
     */
    
    public static function send(array $data): void
    {
        Json::stream($data);
    }
}



/**
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * ◀️ VOLTAR AO TÓPICO ANTERIOR:
 * 🔗 LINK: /galastri/core/output/Json.php
 */
