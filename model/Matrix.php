<?php

namespace models;

/**
 * Clase de persistencia para la creación de las matrices 
 */
class Matrix
{

    const STORAGE_PATH = "persistence/";
    const MAX_MATRIZ_DIMENSION = 100;

    private $x;
    private $y;
    private $z;
    private $hashCode;
    private $matrix;
    private $logOperations;

    /**
     * A partir de un numero entero $dimension construira una matiz en 3d
     * de N*N*N donde N es el valor de la $dimension enviado
     * */
    function __construct($dimension)
    {
        $this->x = $dimension;
        $this->y = $dimension;
        $this->z = $dimension;
        $this->logOperations = array();

        for ($x = 1; $x <= $this->x; $x ++) {
            for ($y = 1; $y <= $this->y; $y ++) {
                for ($z = 1; $z <= $this->z; $z ++) {
                    $this->matrix[$x][$y][$z] = 0;
                }
            }
        }
    }

    /**
     * Obtiene la coordenada indicada
     * @return type
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Obtiene la coordenada indicada
     * @return type
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Obtiene la coordenada indicada
     * @return type
     */
    public function getZ()
    {
        return $this->z;
    }

    /**
     * Obtiene el hasid del objeto
     * @return hash
     */
    public function getHashId()
    {
        //return spl_object_hash($this);
        if (is_null($this->hashCode)) {
            $this->hashCode = hash("sha256", rand());
        }

        return $this->hashCode;
    }

    /**
     * Persiste los valores de la matriz
     * */
    public function save()
    {
        $objId = $this->getHashId();
        $fileName = self::STORAGE_PATH . $objId;

        try {
            file_put_contents($fileName, serialize($this));
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Obtiene la matriz que se ha persistido
     * @param type $obj
     * @return Matrix object
     */
    public static function get($objId)
    {
        $fileName = self::STORAGE_PATH . $objId;

        try {
            $savedObject = file_get_contents($fileName);
            return unserialize($savedObject);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Actualiza el valor de la matriz en las coordenadas indicadas
     * @param type $x int
     * @param type $y int
     * @param type $z int
     * @param type $v int valor a actualizar
     */
    public function update($x, $y, $z, $v)
    {
        $this->matrix[$x][$y][$z] = $v;
        $this->writeOpLog(" UPDATE: $v");
    }

    /**
     * Obtiene la sima de los elementos del arreglo desde las 
     * coordenada n1 hasta las coordenadas n2
     * @param type $x1
     * @param type $y1
     * @param type $z1
     * @param type $x2
     * @param type $y2
     * @param type $z2
     */
    public function query($x1, $y1, $z1, $x2, $y2, $z2)
    {
        $finalValue = 0;

        for ($x = $x1; $x <= $x2; $x ++) {
            for ($y = $y1; $y <= $y2; $y ++) {
                for ($z = $z1; $z <= $z2; $z ++) {
                    $finalValue += $this->matrix[$x][$y][$z];
                }
            }
        }

        $this->writeOpLog(" QUERY: {$finalValue}");
        return $finalValue;
    }

    /**
     * Obtiene la matrix con los valores actualizados
     */
    public function getMatix()
    {
        return $this->matrix;
    }

    /**
     * Writes a log operation
     * @param type $log
     */
    private function writeOpLog($log)
    {
        $this->logOperations[] = $log;
    }

    /**
     * Writes a log operation
     * @param type $log
     */
    public function getOpLog()
    {
        return $this->logOperations;
    }

}
