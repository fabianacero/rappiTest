<?php

namespace controller;

/**
 * Clase que controla las opciones enviadas como parametro
 */
class MainController
{
    function __construct($argv)
    {
        $this->parseOptions($argv);
    }

    /**
     * 	Lee los argumentos que recibe por linea de comandos
     * */
    private function parseOptions($argv)
    {
        if (count($argv) <= 1) {
            //$this->printMessage("This script requires at least one argument [exit]")
            $testNumber = 0;
            while (!is_int($testNumber) || $testNumber == 0) {
                $testNumber = $this->readUserInput("Number of test cases");
                $testNumber = intval($testNumber);

                if ($testNumber == 0) {
                    $this->printMessage("Please indicate a correct test number", false);
                }
            }

            for ($i = 0; $i < $testNumber; $i++) {

                $matrixOps = null;

                while (!preg_match("/(\d+)\s+(\d+)/", $matrixOps)) {

                    $matrixOps = $this->readUserInput("Matriz dimension and operation number [M O]");

                    if (!preg_match("/(\d+)\s+(\d+)/", $matrixOps, $intOpts)) {
                        $this->printMessage("Please indicate a correct dimension and operation number", false);
                    } else {
                        $j = 0;
                        // Obtengo la dimension y numero de operaciones sobre la matriz
                        list($all, $matrixDim, $operNumber) = $intOpts;
                        $matrix = $this->createMatrix($matrixDim);

                        while ($j < $operNumber) {
                            $this->performOperations($matrix);
                            $j++;
                        }
                    }
                }
            }
        }
    }

    /**
     * 	Imprime el mensaje indicado
     * */
    private function printMessage($msg, $exit = true)
    {
        $scriptDescription = "cubeSummation.php [options]\n
	UPDATE: Updates the indicated value in 3D matrix
	QUERY: Queries the indicated value in 3D matrix
	Excercise explanation: https://www.hackerrank.com/challenges/cube-summation";

        if (php_sapi_name() == "cli") {

            print_r("\n Notice: $msg \n\n" . ($exit ? " $scriptDescription" : "" ));

            if ($exit) {
                exit;
            }
        } else {
            return ("<p> $msg</p><p>$scriptDescription</p>");
        }
    }

    /**
     * 	Lee la entrado por consola ingresada por el usuario
     * */
    private function readUserInput($inMsg)
    {
        if (PHP_OS == 'WINNT') {
            echo "$inMsg: ";
            $line = stream_get_line(STDIN, 1024, PHP_EOL);
        } else {
            $line = readline("$inMsg: ");
        }

        $lineInput = readline_info();
        return $lineInput["line_buffer"];
    }

    /**
     * Crea una matriz con la dimension indicada
     * */
    private function createMatrix($matrixDim)
    {
        include ("model/Matrix.php");
        $matrixModel = new \models\Matrix($matrixDim);
        $matrixModel->save();
        return $matrixModel;
    }

    /**
     * Valida la operacion que se realizara sobre la matriz
     */
    private function performOperations($matrix)
    {
        $regExpUpdate = "([u|U])((\s+\d+){4})";
        $regExpQuery = "([q|Q])((\s+\d+){6})";
        $regExp = "/^$regExpUpdate|$regExpQuery$/";
        $operation = null;

        while (!preg_match($regExp, $operation)) {

            $operation = $this->readUserInput("(UPDATE x y z W) or (QUERY x1 y1 z1 x2 y2 z2) [U/Q]");
            if (!preg_match($regExp, $operation)) {
                $this->printMessage("Please indicate a correct format action", false);
            } else {
                $matrixModel = \models\Matrix::get($matrix);
                // UPDATE
                if (preg_match("/$regExpUpdate/", $operation, $mathOpts)) {
                    list($x, $y, $z, $v) = explode(" ", trim($mathOpts[2]));
                    //print_r("$x, $y, $z, $v");
                    $matrixModel->update($x, $y, $z, $v);
                    print_r($matrixModel->getMatix());
                }
                // QUERY
                if (preg_match("/$regExpQuery/", $operation, $mathOpts)) {
                    
                }
            }
        }
    }

}
