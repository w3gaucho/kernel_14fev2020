<?php
namespace Basic;
use Basic\Kernel;
class Migrate extends Kernel{
    var $listOfTableFiles;
    var $tuplesColumnNameVarChar;
    function all(){
        // converter os arquivos de uma pasta em uma lista de arquivos
        $str=$this->root().'app/table';
        $arr=$this->convertFolderToListOfFiles($folder);
        // listar arquivos com as colunas em table/<arquivos>
        $this->setListOfTableFiles($arr);
        // ler arquivos de textos e extrair as tuplas (coluna + tamanho)
        $arr=$this->getListOfTableFiles();
        $arr=$this->convertListOfTableFilesToTuplesColumnNameVarChar($arr);
        $this->setTuplesColumnNameVarChar($arr);
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
    function convertListOfTableFilesToTuplesColumnNameVarChar($arr){
        $columnNameVarChar=false;
        $tablesFolder=$this->root().'app/table/';
        foreach ($arr as $key => $value) {//cada arquivo
            $filename=$tablesFolder.$value;
            if(files_exists($filename)){
                $str=file_get_contents($filename);
                $str=trim($str);
                $arr2=explode(PHP_EOL,$str);
                if(is_array($arr2)){
                    $arr2=array_values($arr2);
                }
                if(is_array($arr2) && count($arr2)>0){
                    foreach ($arr2 as $key => $value) {//cada linha
                        $arr3=explode('_',$value);
                        $columnName=@trim($arr[0]);
                        $varChar=@trim($arr[1]);
                        $columnNameVarChar[$columnName]=$varChar;
                    }
                }
            }
        }
        if($columnNameVarChar){
            foreach ($columnNameVarChar as $columnName => $varChar) {
                if(!is_integer($varChar)){
                    $msg=$varChar.' is not integer';
                    $this->cliFatalError($msg);
                    unset($columnNameVarChar[$columnName]);
                }
                if(!ctype_alpha($columnName)){
                    $msg=$columnName.' is not alpha';
                    $this->cliFatalError($msg);
                    unset($columnNameVarChar[$columnName]);
                }
            }
            if(count($columnNameVarChar)>0){
                return $columnNameVarChar;
            }else{
                $msg='no tables found at '.$tablesFolder;
                $this->cliFatalError($msg);
            }
        }
    }
    function getListOfTableFiles(){
        return $this->listOfTableFiles;
    }
    function getTuplesColumnNameVarChar(){
        return $this->tuplesColumnNameVarChar;
    }
    function setListOfTableFiles($arr){
        $this->listOfTableFiles=$arr;
    }
    function setTuplesColumnNameVarChar($arr){
        $this->tuplesColumnNameVarChar=$arr;
    }
}
?>
