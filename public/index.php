<?php
/*
 * SEJA BEM VINDO AO GALASTRI FRAMEWORK
 * ---
 *
 * Decidi colocar toda a documentação do framework dentro do próprio projeto.
 * 
 * Ela está principalmente em formato de comentários como este aqui. Algumas
 * descrições, porém, podem ser grandes demais por exigirem um nível de
 * explicação maior, então, nestes casos, irei colocar um link interno apontando
 * para um arquivo .md com uma explicação detalhada.
 *
 * Falando nisso, antes de você continuar, é recomendado você compreender o
 * funcionamento do framework. Sugiro ler estes arquivos aqui antes de
 * prosseguir:
 *
 * ***
 * 
 * INTRODUÇÃO AO GALASTRI FRAMEWORK
 * /docs/galastri-framework.md
 *
 * INSTALAÇÃO
 * /docs/instalacao/preparando-o-ambiente-de-desenvolvimento.md
 *
 * ESTRUTURA E FUNCIONAMENTO
 * /docs/instalacao/preparando-o-ambiente-de-desenvolvimento.md
 * 
 * ***
 */

/* 
 * SOBRE ESTE ARQUIVO
 *
 * As requisições encaminhadas pelo servidor para este arquivo iniciam a
 * execução do Galastri Framework.
 *
 * O endereço acessado não precisa corresponder a um arquivo físico. Por
 * exemplo, /clientes/listar pode chegar a este mesmo index.php. A identificação
 * da rota e do controller será feita posteriormente.
 *
 * Este arquivo configura o comportamento inicial dos erros do PHP e carrega o
 * bootstrap, responsável pela preparação do framework.
 */

ini_set('display_errors', true);
error_reporting(E_ALL);

require(__DIR__.'/../galastri/bootstrap/bootstrap.php');
