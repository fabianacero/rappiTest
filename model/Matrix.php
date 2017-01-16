<?php

namespace models;

/**
 * Clase de persistencia para la creación de las matrices 
 */
class Matrix
{

    const storagePath = "persistence/";
    private $x;
    private $y;
    private $z;
    private $matrix;

    /**
     * A partir de un numero entero $dimension construira una matiz en 3d
     * de N*N*N donde N es el valor de la $dimension enviado
     * */
    function __construct($dimension)
    {
        $this->x = $dimension;
        $this->y = $dimension;
        $this->z = $dimension;

        for ($x = 0; $x < $this->x; $x ++) {
            for ($y = 0; $y < $this->y; $y ++) {
                for ($z = 0; $z < $this->z; $z ++) {
                    $this->matrix[$x][$y][$z] = 0;
                }
            }
        }
    }

    /**
     * Persiste los valores de la matriz
     * */
    public function save()
    {
        $objId = spl_object_hash($this);
        $fileName = self::storagePath . $objId;

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
    public static function get($obj)
    {
        $objId = spl_object_hash($obj);
        $fileName = self::storagePath . $objId;
        
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
    public function update($x, $y, $z, $v){
        $this->matrix[$x][$y][$z] = $v;
    }
    
    /**
     * Obtiene la matrix con los valores actualizados
     */
    public function getMatix(){
        return $this->matrix;
    }

}
