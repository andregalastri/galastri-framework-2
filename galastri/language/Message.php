<?php
/**
 * Documentação da classe:
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

    private const ERROR_MESSAGE = 'Ocorreu um problema. Consulte os detalhes nos relatórios de erro.';

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
    
    private const ROUTER_INVALID_BASE_FOLDER = 'Erro de configuração em "%s": O valor "%s" é inválido para o parâmetro "baseFolder". Exemplo válido: "/pasta/subpasta". Acesse a documentação para entender seu funcionamento.';
    
    private const ROUTER_INVALID_TEMPLATE_FILE = 'Erro de configuração em "%s": O valor "%s" é inválido para o parâmetro "templateFile". Exemplo válido: "/pasta/subpasta/arquivo.php". Acesse a documentação para entender seu funcionamento.';
    
    private const ROUTER_INVALID_NAMESPACE = 'Erro de configuração em "%s": O valor "%s" é inválido para o parâmetro "namespace". Exemplo válido: "\\App\\Controller". Acesse a documentação do PHP para entender o padrão válido de namespaces.';
    
    private const ROUTER_INVALID_ENDPOINT_PARAMETERS = 'Erro de configuração em "%s": O valor "%s" é inválido para o parâmetro "parameters". Exemplo válido: "/param1/?param2". Acesse a documentação para entender seu funcionamento.';

    private const ROUTER_MAIN_METHOD_NOT_FOUND = 'Erro de configuração em "%s": O endpoint @main não foi definido no node "%s"';

    
    // private const DEFINITION_INVALID_VALUE = 'Erro:O valor da configuração "%s" é inválido. O valor desta configuração deve ser "%s", mas foi informado "%s".';
    
    // private const DEFINITION_INVALID_TYPE_OF_VALUE = 'A tipagem do valor da configuração "%s" é inválido. A tipagem do valor desta configuração precisa ser "%s", mas foi informado um tipo "".';

    // private const INVALID_CONFIG_FILE = [
    //     'O arquivo de definição de configuração "%s" precisa retornar um array. Um %s foi fornecido.',
    //     'INVALID_CONFIG_FILE',
    // ];

    // private const CONFIG_DEFINITION_NEEDS_TO_BE_ARRAY = [
    //     'A definição "%s" precisa ser uma array.',
    //     'CONFIG_DEFINITION_NEEDS_TO_BE_ARRAY',
    // ];

    // private const CONFIG_DEFINITION_MISSING_REQUIRED_PROPERTY = [
    //     'A propriedade "%s" não foi informada na definição da configuração "%s" no arquivo "%s"',
    //     'CONFIG_DEFINITION_MISSING_REQUIRED_PROPERTY',
    // ];

    // private const CONFIG_DEFINITION_PROPERTY_ALREADY_EXISTS = [
    //     'A propriedade "%s" já existe.',
    //     'CONFIG_DEFINITION_PROPERTY_ALREADY_EXISTS',
    // ];

    // private const CONFIG_DEFINITION_PROPERTY_UNDEFINED = [
    //     'A propriedade "%s" não foi definida.',
    //     'CONFIG_DEFINITION_PROPERTY_UNDEFINED',
    // ];

    // private const WRONG_TYPE_CONFIG = [
    //     'Tipo de configuração incorreto: a configuração "%s" espera ser [%s]. [%s] foi fornecido.',
    //     'WRONG_TYPE_CONFIG',
    // ];

    // private const INVALID_VALUE_CONFIG = [
    //     'Valor de configuração inválido: o valor da configuração "%s" deve ser %s. %s foi fornecido.',
    //     'INVALID_VALUE_CONFIG',
    // ];

    // private const WRONG_TYPE_CONFIG_DEFINITION = [
    //     'Tipo de definição de configuração incorreto: a definição de configuração "%s" espera ser [%s]. [%s] foi fornecido.',
    //     'WRONG_TYPE_CONFIG_DEFINITION',
    // ];

    

    // private const WRONG_TYPE_CONFIG_DEFINITION_VALUE = [
    //     'Tipo de valor de definição de configuração incorreto: o valor da definição de configuração "%s" espera ser [%s]. [%s] foi fornecido.',
    //     'WRONG_TYPE_CONFIG_DEFINITION_VALUE',
    // ];

    // private const CONFIG_NOT_FOUND = [
    //     'Configuração não encontrada: A configuração "%s" não existe.',
    //     'CONFIG_NOT_FOUND',
    // ];

    // private const WRONG_CONFIG_FORMAT = [
    //     'Formato de configuração incorreto: o arquivo de configuração "%s" não está formatado corretamente. Consulte a documentação para entender como configurá-lo.',
    //     'WRONG_CONFIG_FORMAT',
    // ];

    // // REMOVIDO
    // // private const NOT_FOUND_CONFIG_FILE = [
    // //     'Configuração não encontrada: o arquivo de configuração "%s" não existe.',
    // //     'NOT_FOUND_CONFIG_FILE',
    // // ];

    // private const EXCEPTION_PARAMETER_ARRAY_1_NEEDS_2_VALUES = [
    //     'Ao lançar uma "galastri\extension\Exception" usando um array como parâmetro #1, espera-se que a chave [0] seja a mensagem e a chave [1] seja o código.',
    //     'EXCEPTION_PARAMETER_ARRAY_1_NEEDS_2_VALUES',
    // ];

    // private const UNMATCHED_ARGUMENT_COUNT = [
    //     'O parâmetro #2 do método Tools::flagReplacer() espera %s argumento(s). %s fornecido(s).',
    //     'UNMATCHED_ARGUMENT_COUNT',
    // ];

    // private const WRONG_TYPE_EXCEPTION_CODE = [
    //     'Tipo de parâmetro incorreto: galastri\extension\Exception [$code] espera ser [int|string]. [%s] dado.',
    //     'WRONG_TYPE_EXCEPTION_CODE',
    // ];

    // const INVALID_ROUTE_CONFIG_TYPE = [
    //     'INVALID_ROUTE_CONFIG_TYPE',
    //     "The route configuration file needs to return an 'array'."
    // ];

    // const INVALID_ROUTE_CONFIG_ARRAY_KEY_NUMBER = [
    //     'INVALID_ROUTE_CONFIG_ARRAY_KEY_NUMBER',
    //     "The route configuration file needs to contain exactly 1 (one) multidimensional array key, but %s keys were found."
    // ];

    // const INVALID_OFFLINE_MESSAGE_TYPE = [
    //     'INVALID_OFFLINE_MESSAGE_TYPE',
    //     "Invalid type. Type of route parameter 'offlineMessage' need to be 'string'."
    // ];

    // const DEFAULT_OFFLINE_MESSAGE = [
    //     'DEFAULT_OFFLINE_MESSAGE',
    //     "This area is currently offline. Please, try again later."
    // ];

    // const INVALID_AUTH_FAIL_MESSAGE_TYPE = [
    //     'INVALID_AUTH_FAIL_MESSAGE_TYPE',
    //     "Invalid type. Type of route parameter 'authFailMessage' need to be 'string'."
    // ];

    // const DEFAULT_AUTH_FAIL_MESSAGE = [
    //     'DEFAULT_AUTH_FAIL_MESSAGE',
    //     "You aren't authorized to access this area."
    // ];

    // const INVALID_PERMISSION_FAIL_MESSAGE_TYPE = [
    //     'INVALID_PERMISSION_FAIL_MESSAGE_TYPE',
    //     "Invalid type. Type of route parameter 'permissionFailMessage' need to be 'string'."
    // ];

    // const DEFAULT_PERMISSION_FAIL_MESSAGE = [
    //     'DEFAULT_PERMISSION_FAIL_MESSAGE',
    //     "You don't have permission to execute this action."
    // ];

    // const ERROR_404 = [
    //     'ERROR_404',
    //     "Error 404: The requested route or file was not found."
    // ];

    
    // const CONTROLLER_NOT_FOUND = [
    //     'CONTROLLER_NOT_FOUND',
    //     "Requested controller '%s' doesn't exist. Check if the file '%s.php' exists in directory '%s' or if its namespace was correctly set."
    // ];

    // const CONTROLLER_DOESNT_EXTENDS_CORE = [
    //     'CONTROLLER_DOESNT_EXTENDS_CORE',
    //     "Controller '%s' is not extending the core class \galastri\core\Controller. Add the core class to your controller class."
    // ];

    // const CONTROLLER_METHOD_NOT_FOUND = [
    //     'CONTROLLER_METHOD_NOT_FOUND',
    //     "Controller '%s' doesn't have the requested method '@%s'."
    // ];

    // const INVALID_PARAM_TYPE = [
    //     'INVALID_PARAM_TYPE',
    //     "Invalid parameter configuration. Parameter '%s' needs to be '%s'. '%s' given."
    // ];

    // const REQUEST_METHOD_STARTS_WITH_AT = [
    //     'REQUEST_METHOD_STARTS_WITH_AT',
    //     "Request method '%s' need to start with @ as the first character."
    // ];

    // const INVALID_REQUEST_METHOD_NAME = [
    //     'INVALID_REQUEST_METHOD_NAME',
    //     "Request method '%s' has an invalid name."
    // ];

    // const VIEW_UNDEFINED_DATA_KEY = [
    //     'VIEW_UNDEFINED_DATA_KEY',
    //     "Undefined data key to print."
    // ];

    // const VIEW_INVALID_PRINT_DATA = [
    //     'VIEW_INVALID_PRINT_DATA',
    //     "Data cannot be of type 'array' of 'object'. Use 'data' method for these types of values."
    // ];

    // const EMPTY_FILE_PATH = [
    //     'EMPTY_FILE_PATH',
    //     "The path parameter is empty in method '%s'."
    // ];

    // const EMPTY_DIRECTORY_PATH = [
    //     'EMPTY_DIRECTORY_PATH',
    //     "The path parameter is empty in method '%s'."
    // ];

    // const TYPE_DEFAULT_INVALID_MESSAGE = [
    //     'TYPE_DEFAULT_INVALID_MESSAGE',
    //     "Wrong data type. Expecting '%s', but '%s' was given."
    // ];

    // const UNDEFINED_TEMPLATE_FILE = [
    //     'UNDEFINED_TEMPLATE_FILE',
    //     "No template file set to this route. Set a default template in the route configuration."
    // ];

    // const UNDEFINED_BASE_FOLDER = [
    //     'UNDEFINED_BASE_FOLDER',
    //     "No base folder set to this file output. Set a 'baseFolder' parameter that points to the directory where the files are stored."
    // ];

    // const TEMPLATE_FILE_NOT_FOUND = [
    //     'TEMPLATE_FILE_NOT_FOUND',
    //     "Template file '%s' not found."
    // ];

    // const UNDEFINED_EXTENSION_MIME_TYPE = [
    //     'UNDEFINED_EXTENSION_MIME_TYPE',
    //     "Undefined '%s' extension. Define it in the MIME type configuration file, setting the extension and its MIME type."
    // ];

    // const INVALID_MIME_TYPE_FOR_EXTENSION = [
    //     'INVALID_MIME_TYPE_FOR_EXTENSION',
    //     "Invalid MIME type for file extension. Expecting MIME type to be '%s' for the '%s' extension, but '%s' was given."
    // ];

    // const UNDEFINED_FILE_PATH = [
    //     'UNDEFINED_FILE_PATH',
    //     "Undefined file path."
    // ];

    // const VIEW_FILE_NOT_FOUND = [
    //     'VIEW_FILE_NOT_FOUND',
    //     "View file '%s' not found."
    // ];

    // const UNDEFINED_AUTH_TAG = [
    //     'UNDEFINED_AUTH_TAG',
    //     "No authentication tag defined. Define it as parameter of the method."
    // ];

    // const UNCONFIGURED_AUTH_TAG = [
    //     'UNCONFIGURED_AUTH_TAG',
    //     "There is no authTag '%s' configured. Configure it using the 'configure' method before use the 'create' method."
    // ];

    // const UNDEFINED_VALIDATION_ALLOWED_CHARSET = [
    //     'UNDEFINED_VALIDATION_ALLOWED_CHARSET',
    //     "Method 'allowedCharset()' requires one or more charsets defined to work. None was given."
    // ];

    // const UNDEFINED_VALIDATION_REQUIRED_CHARSET = [
    //     'UNDEFINED_VALIDATION_REQUIRED_CHARSET',
    //     "Method 'requiredChars()' needs one or more charsets defined to work. None was given."
    // ];

    // const VALIDATION_CANNOT_BE_NULL = [
    //     'VALIDATION_CANNOT_BE_NULL',
    //     "The value cannot be null."
    // ];

    // const VALIDATION_CANNOT_BE_EMPTY = [
    //     'VALIDATION_CANNOT_BE_EMPTY',
    //     "The value cannot be empty."
    // ];

    // const VALIDATION_STRING_LOWER_CASE_ONLY = [
    //     'VALIDATION_STRING_LOWER_CASE_ONLY',
    //     "Expecting only lower case chars."
    // ];

    // const VALIDATION_STRING_UPPER_CASE_ONLY = [
    //     'VALIDATION_STRING_UPPER_CASE_ONLY',
    //     "Expecting only upper case chars."
    // ];

    // const VALIDATION_STRING_MIN_LENGTH = [
    //     'VALIDATION_STRING_MIN_LENGTH',
    //     "Expecting '%s' minimum char length, but it contains '%s'."
    // ];

    // const VALIDATION_STRING_MAX_LENGTH = [
    //     'VALIDATION_STRING_MAX_LENGTH',
    //     "Expecting '%s' maximum char length, but it contains '%s'."
    // ];

    // const VALIDATION_STRING_INVALID_CHARS = [
    //     'VALIDATION_STRING_INVALID_CHARS',
    //     "The value cannot contain '%s' chars."
    // ];

    // const VALIDATION_STRING_REQUIRED_CHARS = [
    //     'VALIDATION_STRING_REQUIRED_CHARS',
    //     "The value needs to contain '%s' of these chars '%s' but '%s' were informed."
    // ];

    // const TYPE_HISTORY_KEY_NOT_FOUND = [
    //     'TYPE_HISTORY_KEY_NOT_FOUND',
    //     "There is no key '%s' in the history of the type object."
    // ];

    // const TYPE_HISTORY_DISABLED = [
    //     'TYPE_HISTORY_DISABLED',
    //     "Save history is disabled, there is no data to be reverted. If you want enable this, set to 'true' the second constructor parameter in the definition of this object of types."
    // ];

    // const SECURE_RANDOM_GENERATOR_NOT_FOUND = [
    //     'SECURE_RANDOM_GENERATOR_NOT_FOUND',
    //     "No cryptographically secure random string generation function available. You need to check your PHP configuration to make the 'random_bytes()' or 'openssl_random_pseudo_bytes()' functions available."
    // ];

    // const VALIDATION_NUMERIC_MIN_VALUE = [
    //     'VALIDATION_NUMERIC_MIN_VALUE',
    //     "Expecting minimum value '%s' but '%s' were given."
    // ];

    // const VALIDATION_NUMERIC_MAX_VALUE = [
    //     'VALIDATION_NUMERIC_MAX_VALUE',
    //     "Expecting maximum value '%s' but '%s' were given."
    // ];

    // const VALIDATION_UNDEFINED_VALUES_ALLOWED_LIST = [
    //     'VALIDATION_UNDEFINED_VALUES_ALLOWED_LIST',
    //     "It is required to define at least one value in 'allowedValues' method."
    // ];

    // const VALIDATION_NO_VALUE_IN_ALLOWED_LIST = [
    //     'VALIDATION_NO_VALUE_IN_ALLOWED_LIST',
    //     "The value (%s) is not an allowed value. The allowed values are [%s]."
    // ];

    // const VALIDATION_UNDEFINED_VALUES_DENIED_LIST = [
    //     'VALIDATION_UNDEFINED_VALUES_DENIED_LIST',
    //     "It is required to define at least one value in 'deniedValues' method."
    // ];

    // const VALIDATION_VALUE_IN_DENIED_LIST = [
    //     'VALIDATION_VALUE_IN_DENIED_LIST',
    //     "The value (%s) is not an allowed value. The values that aren't allowed are [%s]."
    // ];

    // const VALIDATION_INVALID_DATETIME = [
    //     'VALIDATION_INVALID_DATETIME',
    //     "The value (%s) is an invalid date-time or it doesn't match the format (%s)."
    // ];

    // const VALIDATION_DATETIME_MAX = [
    //     'VALIDATION_DATETIME_MAX',
    //     "The date-time cannot be greater than %s."
    // ];

    // const VALIDATION_DATETIME_MIN = [
    //     'VALIDATION_DATETIME_MIN',
    //     "The date-time cannot be lesser than %s."
    // ];

    // const INVALID_KEY_PARAMETER_TYPE = [
    //     'INVALID_KEY_PARAMETER_TYPE',
    //     "Wrong key type. There is a node in this route whose key was set as %s. Check the route configuration file and define any non-string keys to string type."
    // ];

    // const INVALID_URL_PARAMETERS_TYPE = [
    //     'INVALID_URL_PARAMETERS_TYPE',
    //     "Invalid type. Type of route parameter 'urlParameters' need to be 'string' or NULL."
    // ];

    // const INVALID_TIMEZONE_TYPE = [
    //     'INVALID_TIMEZONE_TYPE',
    //     "Invalid type. Type of route parameter 'timezone' need to be 'string' and cannot be empty."
    // ];

    // const UNDEFINED_TIMEZONE = [
    //     'UNDEFINED_TIMEZONE',
    //     "Undefined route parameter 'timezone'. Set it in the route configuration file. You can omit this parameter to make the default timezone be '%s'"
    // ];

    // const INVALID_TIMEZONE = [
    //     'INVALID_TIMEZONE',
    //     "The timezone informed '%s' isn't valid. Check the valid timezones here: https://www.php.net/manual/en/timezones.php"
    // ];

    // const INVALID_OFFLINE_TYPE = [
    //     'INVALID_OFFLINE_TYPE',
    //     "Invalid type. Type of route parameter 'offline' need to be 'bool'."
    // ];

    // const UNDEFINED_OFFLINE = [
    //     'UNDEFINED_OFFLINE',
    //     "Undefined route parameter 'offline'. Set it in the route configuration file."
    // ];

    // const INVALID_FORCE_REDIRECT_TYPE = [
    //     'INVALID_FORCE_REDIRECT_TYPE',
    //     "Invalid type. Type of route parameter 'forceRedirect' need to be 'string' or 'null'."
    // ];

    // const INVALID_OUTPUT = [
    //     'INVALID_OUTPUT',
    //     "Output %s doesn't exist. The existing outputs are 'view', 'json', 'file' and 'text'."
    // ];

    // const UNDEFINED_OUTPUT = [
    //     'UNDEFINED_OUTPUT',
    //     "Undefined route parameter 'output' to this route. Set it in the route configuration file."
    // ];

    // const INVALID_NOT_FOUND_REDIRECT_TYPE = [
    //     'INVALID_NOT_FOUND_REDIRECT_TYPE',
    //     "Invalid type. Type of route parameter 'notFoundRedirect' need to be 'string' or 'null'."
    // ];

    // const INVALID_BROWSER_CACHE_TYPE = [
    //     'INVALID_BROWSER_CACHE_TYPE',
    //     "Invalid type. Type of route parameter 'browserCache' need to be 'array' or 'null'."
    // ];

    // const INVALID_BROWSER_CACHE_TIME_TYPE = [
    //     'INVALID_BROWSER_CACHE_TIME_TYPE',
    //     "Invalid type. The first key of route parameter 'browserCache' represents the cache time and need to be 'integer'."
    // ];

    // const INVALID_BROWSER_CACHE_HEADER_TYPE = [
    //     'INVALID_BROWSER_CACHE_HEADER_TYPE',
    //     "Invalid type. The second key of route parameter 'browserCache' represents the Cache-Control headers and need to be 'string'."
    // ];

    // const INVALID_CONTROLLER_TYPE = [
    //     'INVALID_CONTROLLER_TYPE',
    //     "Invalid type. Type of parent node parameter 'controller' need to be 'string' or 'null'."
    
    // ];


    
    // const INVALID_NAMESPACE_TYPE = [
    //     'INVALID_NAMESPACE_TYPE',
    //     "Invalid type. Type of route parameter 'namespace' need to be 'string' or 'null'."
    // ];

    // const INVALID_PROJECT_TITLE_TYPE = [
    //     'INVALID_PROJECT_TITLE_TYPE',
    //     "Invalid type. Type of route parameter 'projectTitle' need to be 'string' or 'null'."
    // ];

    // const INVALID_PAGE_TITLE_TYPE = [
    //     'G00INVALID_PAGE_TITLE_TYPE26',
    //     "Invalid type. Type of route parameter 'pageTitle' need to be 'string' or 'null'."
    // ];

    // const INVALID_AUTH_TAG_TYPE = [
    //     'INVALID_AUTH_TAG_TYPE',
    //     "Invalid type. Type of route parameter 'authTag' need to be 'string' or 'null'."
    // ];

    // const INVALID_AUTH_FAIL_REDIRECT_TYPE = [
    //     'INVALID_AUTH_FAIL_REDIRECT_TYPE',
    //     "Invalid type. Type of route parameter 'authFailRedirect' need to be 'string' or 'null'."
    // ];

    // const INVALID_TEMPLATE_FILE_TYPE = [
    //     'INVALID_TEMPLATE_FILE_TYPE',
    //     "Invalid type. Type of route parameter 'templateFile' need to be 'string' or 'null'."
    // ];

    // const INVALID_BASE_FOLDER_TYPE = [
    //     'INVALID_BASE_FOLDER_TYPE',
    //     "Invalid type. Type of route parameter 'baseFolder' need to be 'string' or 'null'."
    // ];

    // const INVALID_DOWNLOADABLE_TYPE = [
    //     'INVALID_DOWNLOADABLE_TYPE',
    //     "Invalid type. Type of route parameter 'downloadable' need to be 'bool' or 'null'."
    // ];

    // const INVALID_ALLOWED_EXTENSIONS_TYPE = [
    //     'INVALID_ALLOWED_EXTENSIONS_TYPE',
    //     "Invalid type. Type of route parameter 'allowedExtensions' need to be 'array' or 'null'."
    // ];

    // const INVALID_ALLOWED_EXTENSION_VALUE_TYPE = [
    //     'INVALID_ALLOWED_EXTENSION_VALUE_TYPE',
    //     "Invalid type. The values of route parameter 'allowedExtensions' need to be 'string'."
    // ];

    // const INVALID_VIEW_PATH_TYPE = [
    //     'INVALID_VIEW_PATH_TYPE',
    //     "Invalid type. Type of route parameter 'viewPath' need to be 'string' or 'null'."
    // ];

    // const INVALID_URL_ROOT_TYPE = [
    //     'INVALID_URL_ROOT_TYPE',
    //     "Invalid type. Type of the URL Root needs to be 'string'."
    // ];

    // const UNDEFINED_URL_ROOT = [
    //     'UNDEFINED_URL_ROOT',
    //     "Undefined URL Root. Set it in the route configuration file."
    // ];

    // const URL_ROOT_MUST_START_WITH_SLASH = [
    //     'URL_ROOT_MUST_START_WITH_SLASH',
    //     "The URL Root needs to start with slash bar (/)."
    // ];

    // const URL_ROOT_MUST_END_WITH_SLASH = [
    //     'URL_ROOT_MUST_END_WITH_SLASH',
    //     "The URL Root needs to end with slash bar (/)."
    // ];

    // const INVALID_DISPLAY_ERRORS_TYPE = [
    //     'INVALID_DISPLAY_ERRORS_TYPE',
    //     "Invalid type. Type of debug parameter 'displayErrors' need to be 'bool'."
    // ];

    // const UNDEFINED_DISPLAY_ERRORS = [
    //     'UNDEFINED_DISPLAY_ERRORS',
    //     "Undefined debug parameter 'displayErrors' to this route. Set it in the route configuration file."
    // ];

    // const INVALID_SHOW_BACKLOG_DATA_TYPE = [
    //     'INVALID_SHOW_BACKLOG_DATA_TYPE',
    //     "Invalid type. Type of debug parameter 'showBacklogData' need to be 'bool'."
    // ];

    // const UNDEFINED_SHOW_BACKLOG_DATA = [
    //     'UNDEFINED_SHOW_BACKLOG_DATA',
    //     "Undefined debug parameter 'showBacklogData' to this route. Set it in the route configuration file."
    // ];

    // const INVALID_PERFORMANCE_ANALYSIS_TYPE = [
    //     'INVALID_PERFORMANCE_ANALYSIS_TYPE',
    //     "Invalid type. Type of debug parameter 'performanceAnalysis' need to be 'bool'."
    // ];

    // const UNDEFINED_PERFORMANCE_ANALYSIS = [
    //     'UNDEFINED_PERFORMANCE_ANALYSIS',
    //     "Undefined route debug 'performanceAnalysis' to this route. Set it in the route configuration file."
    // ];

    // const INVALID_LANGUAGE_TYPE = [
    //     'INVALID_LANGUAGE_TYPE',
    //     "Invalid type. Type of debug parameter 'language' need to be 'string'."
    // ];

    // const LANGUAGE_FILE_NOT_FOUND = [
    //     'LANGUAGE_FILE_NOT_FOUND',
    //     "The language '%s' is invalid. There is no such language file in the directory."
    // ];

    // const UNDEFINED_LANGUAGE = [
    //     'UNDEFINED_LANGUAGE',
    //     "Undefined debug parameter 'language' to this route. Set it in the route configuration file."
    // ];

    // const INVALID_LOCATION_DATA_TYPE = [
    //     'INVALID_LOCATION_DATA_TYPE',
    //     "Invalid type. Location URL or URL Tag value need to be 'string' and cannot be empty."
    // ];

    // const PDO_QUERY_EXECUTION_FAIL = [
    //     'PDO_QUERY_EXECUTION_FAIL',
    //     "Can't execute the query. PDO has returned the following error: '%s'."
    // ];

    // const PDO_CONNECTION_FAIL = [
    //     'PDO_CONNECTION_FAIL',
    //     "Can't connect to database. PDO has returned the following error: '%s'."
    // ];

    // const DATABASE_BIND_PARAMETER_TYPE = [
    //     'DATABASE_BIND_PARAMETER_TYPE',
    //     "Bind parameter #1 need to be string, int or an array."
    // ];

    // const DATABASE_CONNECTION_FAIL_UNDEFINED_PROPERTY = [
    //     'DATABASE_CONNECTION_FAIL_UNDEFINED_PROPERTY',
    //     "Can't connect to database. Property '%s' was not configured."
    // ];

    // const DATABASE_UNINITIALIZED_CLASS = [
    //     'DATABASE_UNINITIALIZED_CLASS',
    //     "Before execute any database method, execute the 'connect()' method."
    // ];

    // const DATABASE_UNAVAILABLE_EXPORT_METHOD = [
    //     'DATABASE_UNAVAILABLE_EXPORT_METHOD',
    //     "Method 'export' isn't available to the '%s' class."
    // ];

    // const INVALID_TEMPLATE_ENGINE_CLASS_VALUE_TYPE = [
    //     'INVALID_TEMPLATE_ENGINE_CLASS_VALUE_TYPE',
    //     "The templateEngineClass parameter needs to be a 'string'."
    // ];

    // const TEMPLATE_ENGINE_CLASS_NOT_FOUND = [
    //     'TEMPLATE_ENGINE_CLASS_NOT_FOUND',
    //     "The Template Engine class '%s' doesn't exist."
    // ];

    // const TEMPLATE_ENGINE_CLASS_DOESNT_EXTENDS_CORE = [
    //     'TEMPLATE_ENGINE_CLASS_DOESNT_EXTENDS_CORE',
    //     "The Template Engine class '%s' needs to extend the class \galastri\extensions\TemplateEngine."
    // ];

    // const TEMPLATE_ENGINE_CLASS_ALREADY_CONSTRUCTED = [
    //     'TEMPLATE_ENGINE_CLASS_ALREADY_CONSTRUCTED',
    //     "You can't rerun the __contruct() method of the TemplateEngine class."
    // ];
}
