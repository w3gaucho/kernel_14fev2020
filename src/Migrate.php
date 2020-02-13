<?php
namespace Basic;
use Basic\Kernel;
use PDO;
class Migrate extends Kernel{
    var $tableFolder;
    var $listOfTableFiles;
    var $tuplesColumnNameVarChar;
    function all($str=false){
        $this->clear();
        if(!$str){
            $str=$this->root().'app/table';
        }
        //verifica se a pasta existe
        if(file_exists($str)){
            //setar a variavel tableFolder
            $this->setTableFolder($str);
            // converter os arquivos de uma pasta em uma lista de arquivos
            $str=$this->getTableFolder();
            $arr=$this->convertFolderToListOfFiles($str);
            // listar arquivos com as colunas em table/<arquivos>
            $this->setListOfTableFiles($arr);
            // ler arquivos de textos e extrair as tuplas (coluna + tamanho)
            $arr=$this->getListOfTableFiles();
            $arr=$this->convertListOfTableFilesToTuplesColumnNameVarChar($arr);
            $this->setTuplesColumnNameVarChar($arr);
            // criar banco de dados
            return $this->createDB($_ENV['DB_NAME']);
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
        }else {
            $msg='folder "'.$str.'" not found';
            $this->cliFatalError($msg);
        }
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
    function convertListOfTableFilesToTuplesColumnNameVarChar(
        $arr
    ){
        $columnNameVarChar=false;
        $tablesFolder=$this->getTableFolder();
        foreach ($arr as $key => $value) {//cada arquivo
            $filename=$tablesFolder.'/'.$value;
            if(file_exists($filename)){
                $str=file_get_contents($filename);
                $str=trim($str);
                $arr2=explode(PHP_EOL,$str);
                if(is_array($arr2)){
                    $arr2=array_values($arr2);
                }
                if(is_array($arr2) && count($arr2)>0){
                    foreach ($arr2 as $key => $value) {//cada linha
                        $arr3=explode('_',$value);
                        $columnName=@trim($arr3[0]);
                        $varChar=@trim($arr3[1]);
                        $columnNameVarChar[$columnName]=$varChar;
                    }
                }
            }
        }
        if($columnNameVarChar){
            foreach ($columnNameVarChar as $columnName => $varChar) {
                if(!ctype_digit($varChar)){
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
    function createDB($str){
        // verificar se um banco de dados existe
        $exists=$this->dbExists($str);
        if($exists){
            return true;
        }else{
            $dsn=$_ENV['DB_TYPE'].":host=".$_ENV['DB_SERVER'].";port=".$_ENV['DB_PORT'];
            $conn = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
            $sql='CREATE DATABASE '.$str;
            if($conn->query($sql)){
                return true;
            }else{
                return false;
            }
        }
    }
    function dbExists($str){
        $dsn=$_ENV['DB_TYPE'].":host=".$_ENV['DB_SERVER'].";port=".$_ENV['DB_PORT'];
        $conn = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $databases = $conn->query('show databases')->fetchAll(PDO::FETCH_COLUMN);
        if(in_array($str,$databases)){
            //existe
            return true;
        }
        else {
            //não existe
            return false;
        }
    }
    function getListOfTableFiles(){
        return $this->listOfTableFiles;
    }
    function getTableFolder(){
        return $this->tableFolder;
    }
    function getTuplesColumnNameVarChar(){
        return $this->tuplesColumnNameVarChar;
    }
    function setListOfTableFiles($arr){
        $this->listOfTableFiles=$arr;
    }
    function setTableFolder($str){
        $this->tableFolder=$str;
    }
    function setTuplesColumnNameVarChar($arr){
        $this->tuplesColumnNameVarChar=$arr;
    }
}
?>
