<?php
/**
 * Documentação:
 * https://github.com/andregalastri/galastri-framework-2/wiki/Classe-Message
 */

namespace galastri\language;


final class Message
{
    private function __construct() {}

    public static function get(string $messageConstName): array
    {
        return [
            self::{$messageConstName},
            $messageConstName
        ];
    }

    private const DEFAULT_ERROR_MESSAGE = 'Ocorreu um problema. Consulte os detalhes nos relatórios de erro.';

    /**
     * Definição de configuração.
     */
    private const DEFINITION_INVALID_NAME = 'Erro na definição de configuração: O nome das configurações devem ser do tipo %s, mas %s é do tipo %s.';

    private const DEFINITION_INVALID_TYPE = 'Erro na definição da configuração "%s": A propriedade "%s" deve ser do tipo %s, mas foi informado um tipo %s.';

    private const DEFINITION_INVALID_TYPE_IN_ARRAY = 'Erro na definição da configuração "%s": A propriedade "%s" deve conter valores do tipo [%s], mas foi informado um tipo %s em uma ou mais chaves.';
    
    private const DEFINITION_INVALID_PROPERTY_VALUE = 'Erro na definição da configuração "%s": A propriedade precisa armazenar um array.';

    private const DEFINITION_MISSING_REQUIRED_PROPERTY = 'Erro na definição da configuração "%s": É obrigatório informar a propriedade "%s".';

    private const DEFINITION_CONFIG_NAME_ALREADY_IN_USE = 'Erro na definição de configuração: O nome de configuração "%s" já está sendo usado.';

    /**
     * Valor de configuração.
     */
    private const CONFIG_INVALID_TYPE = 'Erro na configuração "%s": O valor deve ser do tipo %s, mas foi informado um tipo %s.';

    private const CONFIG_INVALID_TYPE_NOT = 'Erro na configuração "%s": O valor não pode ser do(s) tipo(s) %s.';
    
    private const CONFIG_INVALID_VALUE = 'Erro na configuração "%s": O valor deve ser "%s", mas foi informado o valor "%s".';

    private const CONFIG_INVALID_VALUE_NOT = 'Erro na configuração "%s": O valor não pode ser %s.';

    private const CONFIG_VALUE_MATCHED_REGEX = 'Erro na configuração "%s": O valor %s não pode ser válido na expressão: %s.';

    private const CONFIG_VALUE_MATCHED_REGEX_NOT = 'Erro na configuração "%s": O valor %s precisa ser válido na expressão: %s.';

    private const CONFIG_DOESNT_EXIST = 'Erro: A configuração "%s" não existe.';
    
    private const CONFIG_EXISTS = 'Erro: A configuração "%s" existe.';

    /**
     * Arquivo de configuração.
     */
    private const CONFIG_FILE_RETURNED_INVALID_DATA = 'Erro no arquivo de definição de configuração: O arquivo de "%s" precisa retornar um array.';

    private const CONFIG_FILE_NOT_FOUND = 'Erro: O arquivo de configuração "%s" não existe e é requerido.';
    
    /**
     * Exception
     */
    private const EXCEPTION_INVALID_ARRAY = 'Erro no lançamento de exceção: A array informada no parâmetro 1 precisa ter duas chaves.';

    private const EXCEPTION_INVALID_CODE = 'Erro no lançamento de exceção: O código informado não é válido.';

    /**
     * Ferramentas / Tools.
     */
    private const TOOLS_NUM_OF_FLAGS_UNMATCH_STRING_FLAGS = 'Erro no método flagReplace(): A mensagem possui %s flag(s), mas há apenas %s argumento(s) informado(s).';

    private const TOOLS_INVALID_CHMOD_CODE = 'Erro no método validateChmodCode: O código %s não é válido para o chmod().';
    
    /**
     * Router
     */
    private const ROUTER_INVALID_URL_ROOT = 'Erro de configuração em "%s": A URL Root "%s" não segue um padrão válido de diretórios. Exemplo válido: "/pasta/subpasta". Acesse a documentação para entender seu funcionamento.';
    
    private const ROUTER_INVALID_FILE_FOLDER = 'Erro de configuração em "%s": O valor "%s" é inválido para o parâmetro "fileFolder". Exemplo válido: "/pasta/subpasta". Acesse a documentação para entender seu funcionamento.';
    
    private const ROUTER_INVALID_VIEW_FOLDER = 'Erro de configuração em "%s": O valor "%s" é inválido para o parâmetro "viewFolder". Exemplo válido: "/pasta/subpasta". Acesse a documentação para entender seu funcionamento.';
    
    private const ROUTER_INVALID_VIEW_FILE = 'Erro de configuração em "%s": O valor "%s" é inválido para o parâmetro "viewFile". Exemplo válido: "arquivo.php" ou "/pasta/subpasta/arquivo.php". Acesse a documentação para entender seu funcionamento.';
    
    private const ROUTER_INVALID_TEMPLATE_FILE = 'Erro de configuração em "%s": O valor "%s" é inválido para o parâmetro "templateFile". Exemplo válido: "/pasta/subpasta/arquivo.php". Acesse a documentação para entender seu funcionamento.';
    
    private const ROUTER_INVALID_NAMESPACE = 'Erro de configuração em "%s": O valor "%s" é inválido para o parâmetro "namespace". Exemplo válido: "\\App\\Controller". Acesse a documentação do PHP para entender o padrão válido de namespaces.';
    
    private const ROUTER_INVALID_ENDPOINT_PARAMETERS = 'Erro de configuração em "%s": O valor "%s" é inválido para o parâmetro "parameters". Exemplo válido: "/param1/?param2". Acesse a documentação para entender seu funcionamento.';

    private const ROUTER_MAIN_METHOD_NOT_FOUND = 'Erro de configuração em "%s": O endpoint @main não foi definido no node "%s"';

    /**
     * Controller
     */
    private const CONTROLLER_NOT_FOUND = 'Erro de controller: A controller "%s" não foi encontrada em "%s".';

    private const CONTROLLER_MUST_EXTEND_MODULE = 'Erro de controller: A controller "%s" precisa herdar o módulo "galastri\modules\Controller".';
    
    private const CONTROLLER_METHOD_NOT_FOUND = 'Erro de controller: A controller "%s" não possui o método "%s".';
    
    
    /**
     * Output
     */
    private const OUTPUT_TEMPLATE_FILE_NOT_FOUND = 'Erro de saída (output): O arquivo de template "%s" não foi encontrado.';
    
    private const OUTPUT_VIEW_FILE_NOT_FOUND = 'Erro de saída (output): A view "%s" não foi encontrada.';
    
    private const OUTPUT_UNDEFINED_FILE_FOLDER = 'Erro de saída (file output): É necessário informar um parâmetro "fileFolder" para esta rota.';
    
    private const OUTPUT_INVALID_FILE_EXTENSION = 'Erro de saída (file output): Esta rota não permite exibir arquivos com extensão "%s", apenas "%s".';
    
    private const OUTPUT_UNDEFINED_FILE_MIME_EXTENSION = 'A extensão "%s" não possui um tipo MIME definido na configuração.';
    
    private const OUTPUT_UNLISTED_FILE_MIME_TYPE = 'O tipo MIME "%s" não foi configurado como um tipo válido para arquivos "%s".';
    
    /**
     * Redirect
     */
    private const REDIRECT_INVALID_STATUS_CODE = 'O status de redirecionamento %s é inválido. Os códigos válidos são: %s.';

    /**
     * Authentication
     */
    private const AUTHENTICATION_UNDEFINED_AUTH_TAG = 'Erro de autenticação: Nenhuma tag de autenticação foi definida. Defina-o como parâmetro do método.';

    private const AUTHENTICATION_UNCONFIGURED_AUTH_TAG = 'Erro de autenticação: A tag de autenticação "%s" não foi configurada. Configure-a usando o método "configure" antes de usar o método "create".';

    private const AUTHENTICATION_UNCONFIGURED_SECRET_KEY = 'Erro de autenticação: A chave secreta não foi configurada. Configure-a usando o método "configure" antes de usar o método "create".';
    
    /**
     * Validation
     */

    private const VALIDATION_FAIL = 'Os dados informados são inválidos.';
    
    private const VALIDATION_INVALID_DATETIME = 'Erro de validação: A data/hora "%s" não condiz com o formato "%s".';
    
    private const VALIDATION_WRONG_CONFIG = 'Erro de validação: O método %s não foi configurado corretamente.';
    
    private const VALIDATION_INVALID_MODE = 'Erro de validação: O modo %s não existe.';
}
