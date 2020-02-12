<?php
namespace Basic;
use Basic\Kernel;
class Migrate extends Kernel{
    var $listOfTableFiles;
    function all(){
        // converter os arquivos de uma pasta em uma lista de arquivos
        $folder=$this->root().'app/table';
        $listOfTableFiles=$this->convertFolderToListOfFiles($folder);
        $this->setListOfTableFiles($listOfTableFiles);
        // listar arquivos com as colunas em table/<arquivos>
        //$this->setListOfTableFiles($listOfTableFiles);
        // ler arquivos de textos e extrair as tuplas (coluna + tamanho)
        // ^migrate->setTuplesColumnNameVarchar($arr)
        // verificar se um banco de dados existe
        // ^migrate->databaseExists($str)
        // criar banco de dados
        // ^migrate->createDatabase($str)
        // verificar se uma tabela existe
        // ^migrate->tableExists($str)
        // criar uma tabela
        // ^migrate->createTable($str)
        // apagar uma tabela
        // ^migrate->deleteTable($str)
        // verificar se uma coluna existe
        // ^migrate->columnExists($str) <- retorna o length caso ela exista
        // criar uma coluna
        // ^migrate->createColumn($str)
        // alterar o tamanho da coluna (ex: varchar(n))
        // ^migrate->changeColumnLength($str,$int)
        // apagar uma coluna
        // ^migrate->deleteColumn($str)
    }
    function convertFolderToListOfFiles($str){
        $folder=$str;
        $ignored=array('.', '..', '.svn', '.htaccess');
        $migrations=false;
        foreach (scandir($folder) as $key => $value) {
            if (in_array($value, $ignored)) {
                continue;
            }
            $migrations[] = $value;
        }
        return $migrations;
    }
    function setListOfTableFiles($arr){
        $this->listOfTableFiles=$arr;
    }
}
?>
