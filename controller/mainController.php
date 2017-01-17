<?php

namespace controller;

include ("model/Matrix.php");

/**
 * Clase que controla las opciones enviadas como parametro
 */
class MainController
{

    const MAX_TEST_NUMBER_OPTIONS = 50;
    const MAX_TEST_OPERATIONS = 1000;

    private $savedHashes;

    function __construct($argv)
    {
        $this->parseOptions($argv);
        $this->savedHashes = array();
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

                if ($testNumber == 0 || $testNumber > self::MAX_TEST_NUMBER_OPTIONS) {
                    $this->printMessage("Please indicate a correct test number between 1 and 50", false);
                }
            }

            for ($i = 0; $i < $testNumber; $i++) {

                $matrixOps = null;
                $correctOperation = true;

                while (!preg_match("/(\d+)\s+(\d+)/", $matrixOps) || !$correctOperation) {

                    $matrixOps = $this->readUserInput("\nMatriz dimension and operation number [M O]");

                    if (!preg_match("/(\d+)\s+(\d+)/", $matrixOps, $intOpts)) {
                        $this->printMessage("Please indicate a correct dimension and operation number", false);
                    } else {
                        $j = 0;
                        // Obtengo la dimension y numero de operaciones sobre la matriz
                        list($all, $matrixDim, $operNumber) = $intOpts;

                        $maxOpQuantity = self::MAX_TEST_OPERATIONS;
                        if ($operNumber > self::MAX_TEST_OPERATIONS) {
                            $this->printMessage("Maximum operation quantity {$maxOpQuantity}", false);
                            $correctOperation = false;
                            continue;
                        }

                        $maximunDimension = \models\Matrix::MAX_MATRIZ_DIMENSION;
                        if ($matrixDim > $maximunDimension) {
                            $this->printMessage("Maximum matriz dimension is {$maximunDimension}", false);
                            $correctOperation = false;
                            continue;
                        }

                        $correctOperation = true;
                        $matrix = $this->createMatrix($matrixDim);

                        while ($j < $operNumber) {
                            $this->performOperations($matrix);
                            $j++;
                        }
                    }
                }
            }
        }
        $this->printOutput();
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
        $matrixModel = new \models\Matrix($matrixDim);
        $this->savedHashes[] = $matrixModel->getHashId();
        $matrixModel->save();
        return $matrixModel;
    }

    /**
     * Valida la operacion que se realizara sobre la matriz
     */
    private function performOperations($matrixModel)
    {
        $regExpUpdate = "([u|U])((\s+\d+){4})";
        $regExpQuery = "([q|Q])((\s+\d+){6})";
        $regExp = "/^$regExpUpdate|$regExpQuery$/";
        $operation = null;
        $correctDimension = true;

        while (!preg_match($regExp, $operation) || !$correctDimension) {
            $correctDimension = true;
            $operation = $this->readUserInput("(UPDATE x y z W) or (QUERY x1 y1 z1 x2 y2 z2) [U/Q]");

            if (!preg_match($regExp, $operation)) {
                $this->printMessage("Please indicate a correct format action", false);
            } else {
                $coordinatesMessage = "Please set a correct matix coordinates";
                // UPDATE
                if (preg_match("/$regExpUpdate/", $operation, $mathOpts)) {
                    $coordinates = explode(" ", trim($mathOpts[2]));
                    list($x, $y, $z, $v) = $coordinates;

                    if (in_array(0, $coordinates)) {
                        $correctDimension = false;
                    }
                    
                    if ($x > $matrixModel->getX() || $y > $matrixModel->getY() || $z > $matrixModel->getZ()) {
                        $correctDimension = false;
                    }
                    
                    if (!$correctDimension) {
                        $this->printMessage($coordinatesMessage, false);
                        continue;
                    }

                    $matrixModel->update($x, $y, $z, $v);
                }
                // QUERY
                if (preg_match("/$regExpQuery/", $operation, $mathOpts)) {
                    $coordinates = explode(" ", trim($mathOpts[2]));
                    list($x1, $y1, $z1, $x2, $y2, $z2) = $coordinates;
                    
                    if (in_array(0, $coordinates)) {
                        $correctDimension = false;
                    }
                    
                    if ($x2 > $matrixModel->getX() || $y2 > $matrixModel->getY() || $z2 > $matrixModel->getZ()) {
                        $correctDimension = false;
                    }

                    if (!$correctDimension) {
                        $this->printMessage($coordinatesMessage, false);
                        continue;
                    }

                    if ($x1 > $x2 || $y1 > $y2 || $z1 > $z2) {
                        $this->printMessage("Initial coordinates must be lower than final coordinates", false);
                        $correctDimension = false;
                        continue;
                    }

                    $correctDimension = true;
                    $matrixModel->query($x1, $y1, $z1, $x2, $y2, $z2);
                }

                $matrixModel->save();
            }
        }
    }

    /**
     * Imprime el resultado de la ejecucion del script
     */
    private function printOutput()
    {
        print(PHP_EOL . " OUTPUT LOG " . PHP_EOL);

        foreach ($this->savedHashes as $hasId) {
            $recoveredObject = \models\Matrix::get($hasId);

            print("::::::::::::::::::::::::::::::" . PHP_EOL);

            foreach ($recoveredObject->getOpLog() as $log) {
                print($log . PHP_EOL);
            }

            print("::::::::::::::::::::::::::::::");
        }
    }

}
