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
    private const DEFINITION_INVALID_NAME = 'Erro na definição de configuração: O nome das configurações devem ser do tipo {{}}, mas {{}} é do tipo {{}}.';

    private const DEFINITION_INVALID_VALUE_IN_ARRAY = 'Erro na definição da configuração "{{}}": A propriedade "{{}}" precisa ter um dos valores [{{}}], mas foi informado "{{}}".';

    private const DEFINITION_INVALID_TYPE = 'Erro na definição da configuração "{{}}": A propriedade "{{}}" deve ser do tipo {{}}, mas foi informado um tipo {{}}.';

    private const DEFINITION_INVALID_TYPE_IN_ARRAY = 'Erro na definição da configuração "{{}}": A propriedade "{{}}" deve conter valores do tipo [{{}}], mas foi informado um tipo {{}} em uma ou mais chaves.';

    private const DEFINITION_INVALID_PROPERTY_VALUE = 'Erro na definição da configuração "{{}}": A propriedade precisa armazenar um array.';

    private const DEFINITION_MISSING_REQUIRED_PROPERTY = 'Erro na definição da configuração "{{}}": É obrigatório informar a propriedade "{{}}".';

    private const DEFINITION_CONFIG_NAME_ALREADY_IN_USE = 'Erro na definição de configuração: O nome de configuração "{{}}" já está sendo usado.';

    /**
     * Valor de configuração.
     */
    private const CONFIG_INVALID_TYPE = 'Erro na configuração "{{}}": O valor deve ser do tipo {{}}, mas foi informado um tipo {{}}.';

    private const CONFIG_INVALID_TYPE_NOT = 'Erro na configuração "{{}}": O valor não pode ser do(s) tipo(s) {{}}.';

    private const CONFIG_INVALID_VALUE = 'Erro na configuração "{{}}": O valor deve ser "{{}}", mas foi informado o valor "{{}}".';

    private const CONFIG_INVALID_VALUE_NOT = 'Erro na configuração "{{}}": O valor não pode ser {{}}.';

    private const CONFIG_VALUE_MATCHED_REGEX = 'Erro na configuração "{{}}": O valor {{}} não pode ser válido na expressão: {{}}.';

    private const CONFIG_VALUE_MATCHED_REGEX_NOT = 'Erro na configuração "{{}}": O valor {{}} precisa ser válido na expressão: {{}}.';

    private const CONFIG_DOESNT_EXIST = 'Erro: A configuração "{{}}" não existe.';

    private const CONFIG_EXISTS = 'Erro: A configuração "{{}}" existe.';

    /**
     * Arquivo de configuração.
     */
    private const CONFIG_FILE_RETURNED_INVALID_DATA = 'Erro no arquivo de definição de configuração: O arquivo de "{{}}" precisa retornar um array.';

    private const CONFIG_FILE_NOT_FOUND = 'Erro: O arquivo de configuração "{{}}" não existe e é requerido.';

    /**
     * Exception
     */
    private const EXCEPTION_INVALID_ARRAY = 'Erro no lançamento de exceção: A array informada no parâmetro 1 precisa ter duas chaves.';

    private const EXCEPTION_INVALID_CODE = 'Erro no lançamento de exceção: O código informado não é válido.';

    /**
     * Ferramentas / Tools.
     */
    private const TOOLS_PLACEHOLDER_ANALYSIS_FAILED = 'Não foi possível analisar os placeholders.';

    private const TOOLS_INVALID_PLACEHOLDER = 'Placeholder inválido: {{}}';

    private const TOOLS_MIXED_PLACEHOLDER_MODES = 'Não misture placeholders sequenciais, posicionais e nomeados na mesma mensagem.';

    private const TOOLS_MISSING_PLACEHOLDER_VALUE = 'Não foi informado um valor para o placeholder {{}}.';

    private const TOOLS_INVALID_PLACEHOLDER_POSITION = 'Posição inválida no placeholder {{}}.';

    private const TOOLS_INVALID_PLACEHOLDER_VALUE = 'O valor de um placeholder deve ser escalar, null ou implementar Stringable.';

    private const TOOLS_PLACEHOLDER_REPLACEMENT_FAILED = 'Não foi possível substituir os placeholders.';

    private const TOOLS_INVALID_CHMOD_CODE = 'Erro no método validateChmodCode: O código {{}} não é válido para o chmod().';

    private const TOOLS_INVALID_WRITE_MODE = 'Erro no método writeFile: O modo de escrita {{}} é inválido.';

    /**
     * Router
     */
    private const ROUTER_INVALID_URL_ROOT = 'Erro de configuração em "{{}}": A URL Root "{{}}" não segue um padrão válido de diretórios. Exemplo válido: "/pasta/subpasta". Acesse a documentação para entender seu funcionamento.';

    private const ROUTER_INVALID_FILE_FOLDER = 'Erro de configuração em "{{}}": O valor "{{}}" é inválido para o parâmetro "fileFolder". Exemplo válido: "/pasta/subpasta". Acesse a documentação para entender seu funcionamento.';

    private const ROUTER_INVALID_VIEW_FOLDER = 'Erro de configuração em "{{}}": O valor "{{}}" é inválido para o parâmetro "viewFolder". Exemplo válido: "/pasta/subpasta". Acesse a documentação para entender seu funcionamento.';

    private const ROUTER_INVALID_VIEW_FILE = 'Erro de configuração em "{{}}": O valor "{{}}" é inválido para o parâmetro "viewFile". Exemplo válido: "arquivo.php" ou "/pasta/subpasta/arquivo.php". Acesse a documentação para entender seu funcionamento.';

    private const ROUTER_INVALID_TEMPLATE_FILE = 'Erro de configuração em "{{}}": O valor "{{}}" é inválido para o parâmetro "templateFile". Exemplo válido: "/pasta/subpasta/arquivo.php". Acesse a documentação para entender seu funcionamento.';

    private const ROUTER_INVALID_NAMESPACE = 'Erro de configuração em "{{}}": O valor "{{}}" é inválido para o parâmetro "namespace". Exemplo válido: "\\App\\Controller". Acesse a documentação do PHP para entender o padrão válido de namespaces.';

    private const ROUTER_INVALID_ENDPOINT_PARAMETERS = 'Erro de configuração em "{{}}": O valor "{{}}" é inválido para o parâmetro "parameters". Exemplo válido: "/param1/?param2". Acesse a documentação para entender seu funcionamento.';

    private const ROUTER_MAIN_METHOD_NOT_FOUND = 'Erro de configuração em "{{}}": O endpoint @main não foi definido no node "{{}}"';

    /**
     * Controller
     */
    private const CONTROLLER_NOT_FOUND = 'Erro de controller: A controller "{{}}" não foi encontrada em "{{}}".';

    private const CONTROLLER_MUST_EXTEND_MODULE = 'Erro de controller: A controller "{{}}" precisa herdar o módulo "galastri\modules\Controller".';

    private const CONTROLLER_METHOD_NOT_FOUND = 'Erro de controller: A controller "{{}}" não possui o método "{{}}".';


    /**
     * Output
     */
    private const OUTPUT_TEMPLATE_FILE_NOT_FOUND = 'Erro de saída (output): O arquivo de template "{{}}" não foi encontrado.';

    private const OUTPUT_VIEW_FILE_NOT_FOUND = 'Erro de saída (output): A view "{{}}" não foi encontrada.';

    private const OUTPUT_UNDEFINED_FILE_FOLDER = 'Erro de saída (file output): É necessário informar um parâmetro "fileFolder" para esta rota.';

    private const OUTPUT_INVALID_FILE_EXTENSION = 'Erro de saída (file output): Esta rota não permite exibir arquivos com extensão "{{}}", apenas "{{}}".';

    private const OUTPUT_UNDEFINED_FILE_MIME_EXTENSION = 'A extensão "{{}}" não possui um tipo MIME definido na configuração.';

    private const OUTPUT_UNLISTED_FILE_MIME_TYPE = 'O tipo MIME "{{}}" não foi configurado como um tipo válido para arquivos "{{}}".';

    /**
     * Redirect
     */
    private const REDIRECT_INVALID_STATUS_CODE = 'O status de redirecionamento {{}} é inválido. Os códigos válidos são: {{}}.';

    /**
     * Authentication
     */
    private const AUTHENTICATION_UNDEFINED_AUTH_TAG = 'Erro de autenticação: Nenhuma tag de autenticação foi definida. Defina-o como parâmetro do método.';

    private const AUTHENTICATION_UNCONFIGURED_AUTH_TAG = 'Erro de autenticação: A tag de autenticação "{{}}" não foi configurada. Use setAuthTag() antes de create().';

    private const AUTHENTICATION_UNCONFIGURED_SECRET_KEY = 'Erro de autenticação: A chave secreta não foi configurada. Configure-a usando o método "configure" antes de usar o método "create".';

    private const AUTHENTICATION_SESSION_REGENERATION_FAILED = 'Não foi possível renovar a sessão de autenticação.';

    /**
     * Validation
     */
    private const VALIDATION_FAIL = 'Os dados informados são inválidos.';

    private const VALIDATION_INVALID_DATETIME = 'Erro de validação: A data/hora "{{}}" não condiz com o formato "{{}}".';

    private const VALIDATION_WRONG_CONFIG = 'Erro de validação: O método {{}} não foi configurado corretamente.';

    private const VALIDATION_INVALID_MODE = 'Erro de validação: O modo {{}} não existe.';


    /**
     * Database
     */
    private const DATABASE_CONFIG_DOESNT_EXIST = 'Erro de banco de dados: A configuração "{{}}" não existe.';

    private const DATABASE_CONFIG_MISSING_REQUIRED_PROPERTY = 'Erro de banco de dados: A configuração "{{}}" não possui a propriedade "{{}}".';

    private const DATABASE_MISSING_REQUIRED_PROPERTY = 'Erro de banco de dados: A propriedade "{{}}" precisa ser configurada.';

    private const DATABASE_DISCONNECTED = 'Erro de banco de dados: O banco de dados não está conectado.';

    private const DATABASE_ALREADY_CONNECTED = 'Erro de banco de dados: Desconecte o banco atual antes de abrir outra conexão.';

    private const DATABASE_BIND_PARAMETER_TYPE = 'Erro de banco de dados: O tipo do parâmetro "{{}}" não é válido. Os tipos válidos são: "{{}}".';

    private const DATABASE_RESULT_DOESNT_EXIST = 'Erro de banco de dados: O nome "{{}}" para o resultado da consulta não existe.';

    /**
     * Browser Cache
     */
    private const CACHE_INVALID_DURATION = 'Erro de cache: A configuração "browserCache" precisa que o primeiro valor seja um número inteiro maior que zero.';

    private const CACHE_INVALID_CACHE_CONTROL = 'Erro de cache: A configuração "browserCache" precisa que o segundo valor seja uma string.';

    private const VIEW_INVALID_PRINT_DATA = 'Erro no PHP View Engine: O tipo de dado no print() é inválido.';
}
