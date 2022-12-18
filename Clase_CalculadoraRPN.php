
<?php

session_start();

/*
    Implementación de un stack (LIFO: Last in first out)
    @author Adriana R.F. - UO282798
*/
class StackLIFO {

    private $stack;

    // Constructor de la pila
    function __construct(){
        $this->stack = [];
    }

    // Borra el contenido de la pila
    public function borrar(){
        $this->stack = [];
    }

    // Devuelve el tamaño del stack
    public function length(){
        return count($this->stack);
    }

    // Devuelve si está vacía
    public function isEmpty(){
        return $this->length()==0;
    }

    // Devuelve el array entero
    public function get(){
        return $this->stack;
    }

    // Push: introduce un valor en el tope de la pila [FINAL]
    public function push($valor){
        array_push($this->stack,$valor);
    }

    // Pop: saca un valor del tope de la pila [FINAL]
    // Último en entrar: primero en salir
    public function pop(){
        return array_pop($this->stack); // returns undefined if empty
    }

    // Muestra los valores que se encuentran en la pila
    public function show(){
        // Los imprime al revés
        $txt = "";
        for ($i= $this->length()-1; $i>=0; $i--){
            $txt = $txt . $this->stack[$i] . "\r\n";
        }
        return $txt;
    }

}


/*
    Calculadora RPN con funciones básicas y científicas,
    implementada a través de una pila LIFO.
    @author Adriana R.F. - UO282798
*/
class CalculadoraRPN {

    private $pila;
    private $operacion;
    private $degrees;
    private $rad;
    private $grad;
    private $hyper;
    private $shift;
    private $signo;
    private $exponent;
    private $next;



    function __construct(){
        $this->pila = new StackLIFO();
        $this->operacion = ""; // Todavía no se ha asignado ninguna operación

        // ---- Variables de estado de la calculadora
                    
        // Grados
        $this->degrees = true;
        $this->rad = false;
        $this->grad = false;

        // Trigonometría
        $this->hyper = false;
        $this->shift = false; // Para inversas

        // Signo de lo último introducido
        $this->signo = false;

        // Exponente
        $this->exponent = false;

        // Siguiente número
        $this->next = false;
    }


    /*
    ------------------------------------------------------------------------------------
    --> ESCRITURA */

    // Escribir la pantalla en el HTML, usando selectores CSS
    public function escribir(){
        return $this->pila->show();
    }
    

    public function digitos($n){ // Override del método de dígitos
        if (!$this->next){
            $valor = "";
            if ($this->pila->isEmpty())
                $valor = "";
            else
                $valor = (string)($this->pila->pop());
            $valor = $valor . (string)$n;
            $this->pila->push($valor);
        } else {
            $this->pila->push((string)($n));
            $this->next = false;
        }
    }

    public function punto(){

        if (!$this->next){
            $valor = "";
            if ($this->pila->isEmpty())
                $valor = "";
            else
                $valor = (string)($this->pila->pop());
            $this->pila->push($valor . ".");
        } else  {
            $this->pila->push(".");
            $this->next = false;
        }
    }

    // Pasa a una nueva línea, y realiza el cálculo
    public function enter(){ 
        $this->next = true;

        if ($this->operacion != ""){ // Si lo que se ha definido es una operación
            $this->calcular();
            $this->operacion = "";
        }

    }

    /*
    ------------------------------------------------------------------------------------
    --> ESCOGER OPERADOR */
    public function operador($x){
        $this->operacion = $x;
    }


    // Realiza el cálculo
    public function calcular(){

        if (!$this->pila->isEmpty()){
            switch($this->operacion){
                case "suma": $this->suma(); break;
                case "resta": $this->resta(); break;
                case "mult": $this->multiplicacion(); break;
                case "div": $this->division(); break;
                case "x2": $this->pow(true); break;
                case "xy": $this->pow(false); break;
                case "sin": $this->sin(); break;
                case "cos": $this->cos(); break;
                case "tan": $this->tan(); break;
                case "sqrt": $this->sqrt(); break;
                case "log": $this->log(); break;
                case "mod": $this->mod(); break;
                case "fact": $this->factorial(); break;
    
                default: break; // No se hace nada si no hay un operador definido
            }
    
        }
    }


    /*
    ------------------------------------------------------------------------------------
    */


    public function hyp(){ // Cambia a funciones hiperbólicas o no
        $this->hyper = !$this->hyper;
    }

    
    public function inverse(){ // Cambia funciones trigonométricas a su inversa o no
        $this->shift = !$this->shift;
    }

    // Devuelve el nombre para los botones de las funciones trigonométricas
    public function getSinName(){
        if ($this->hyper){
            if (!$this->shift){
                return "sinh";
            } else {
                return "arcsinh";
            }
        } else {
            if (!$this->shift){
            return "sin";
            } else {
                return "arcsin";
            }
        }
    }

    public function getCosName(){
        if ($this->hyper){
            if (!$this->shift){
                return "cosh";
            } else {
                return "arccosh";
            }
        } else {
            if (!$this->shift){
            return "cos";
            } else {
                return "arccos";
            }
        }
    }

    public function getTanName(){
        if ($this->hyper){
            if (!$this->shift){
                return "tanh";
            } else {
                return "arctanh";
            }
        } else {
            if (!$this->shift){
            return "tan";
            } else {
                return "arctan";
            }
        }
    }



    public function cambiarSigno(){ // Cambia el signo del número en pantalla
        if (!$this->pila->isEmpty()){
            $p = (string)($this->pila->pop());
            if (strlen($p) > 0){
                if ($p[0] === "-"){
                    $p = substr($p,1,strlen($p));
                } else {
                    $p = "-" . $p;
                }
                $this->pila->push($p);
            }    
        }
    }
    

    /*
    ------------------------------------------------------------------------------------
    */


    public function sin(){
        // Texto dado en radianes
        $rad = floatval($this->pila->pop()); //
        if ($this->degrees){ // Grados
            $rad *= pi() / 180;
        } else if ($this->grad){ // Gradianes
            $rad *= pi() / 200;
        } 

        // Funcion trigonometrica concreta
        $result = 0;
        if ($this->shift) { // Inversa
            if ($this->hyper){ // Hiperbólica
                $result = asinh($rad);
            } else {
                $result = asin($rad);
            }
        } else {
            if ($this->hyper){
                $result = sinh($rad);
            } else {
                $result = sin($rad);
            }
        }

        $this->pila->push($result);
        
    }


    public function cos(){
        // Texto dado en radianes
        $rad = floatval($this->pila->pop());
        if ($this->degrees){
            $rad *= pi() / 180;
        } else if ($this->grad){
            $rad *= pi() / 200;
        } 

        // Funcion trigonometrica concreta
        $result = 0;
        if ($this->shift) { // Inversa
            if ($this->hyper){ // Hiperbólica
                $result = acosh($rad);
            } else {
                $result = acos($rad);
            }
        } else {
            if ($this->hyper){
                $result = cosh($rad);

            } else {
                $result = cos($rad);
            }
        }

        $this->pila->push($result);
    }


    public function tan(){
        // Texto dado en radianes
        $rad = floatval($this->pila->pop());
        if ($this->degrees){
            $rad *= pi() / 180;
        } else if ($this->grad){
            $rad *= pi() / 200;
        } 

        // Funcion trigonometrica concreta
        $result = 0;
        if ($this->shift) { // Inversa
            if ($this->hyper){ // Hiperbólica
                $result = atanh($rad);
            } else {
                $result = atan($rad);
            }
        } else {
            if ($this->hyper){
                $result = tanh($rad);

            } else {
                $result = tan($rad);
            }
        }

        $this->pila->push($result);
        
    }


    public function deg(){
        if ($this->degrees){
            $this->degrees = false;
            $this->rad = true;
            $this->grad = false;

        } else if ($this->rad){
            $this->degrees = false;
            $this->rad = false;
            $this->grad = true;

        } else {
            $this->degrees = true;
            $this->rad = false;
            $this->grad = false;
        }
    }

    public function getDegName(){
        if ($this->degrees){
            return "DEG";
        } else if ($this->rad){
            return "RAD";
        } else {
            return "GRAD";
        }
    }

    /*
    ------------------------------------------------------------------------------------
    */

    public function factorial(){
        $i = 1;
        $num = floatval($this->pila->pop());
        for($j=2;$j<=$num;$j++){
            $i *= $j;
        }
        $this->pila->push($i);
    }


    /*
    ------------------------------------------------------------------------------------
    */

    // Borra todo
    public function onc(){
        $this->pila->borrar();
        
    }

    public function clearEntry(){ // Borra la última entrada en pantalla
        // Si se ha introducido una operación, se borra la operación
        if ($this->operacion != ""){
            $this->operacion = "";
        } else { // Si no, se borra el últ. número entero introducido
            $this->pila->pop(); 
        }
    }

    public function clearError(){
        $p = $this->pila->pop();
        if ($p != ""){
            $p = substr($p,0, strlen($p)-1);
        }
        $this->pila->push($p);
    }

    /*
    ------------------------------------------------------------------------------------
    --> Operaciones unarias */

    // Calcula una potencia
    public function pow($powerOfTwo){ // Potencias: de 2 o de y

        if ($powerOfTwo){ // Unario
            if (!$this->pila->isEmpty()){
                $base = floatval($this->pila->pop());
                $this->pila->push(pow($base,2));
            }
        
        } else {
            if ($this->pila->length()>1){
                $y = floatval($this->pila->pop());
                $base = floatval($this->pila->pop());
                $this->pila->push(pow($base,$y));
            }
        }

    }

    // Calcula el logaritmo en base 10 del núm en pantalla
    public function log(){
        if (!$this->pila->isEmpty()){
            $num = floatval($this->pila->pop());
            $this->pila->push(log10($num)); // base 10
            
        }
    }

    // Calcula la raíz cuadrada
    public function sqrt(){
        if (!$this->pila->isEmpty()){
            $num = floatval($this->pila->pop());
            $this->pila->push($this->sqrt($num));
            
        }
    }

    // Operaciones binarias

    public function suma(){
        if ($this->pila->length()>1){
            $b = floatval($this->pila->pop());
            $a = floatval($this->pila->pop());

            $this->pila->push($a+$b);
            
        }
    }

    public function resta(){
        if ($this->pila->length()>1){
            $b = floatval($this->pila->pop());
            $a = floatval($this->pila->pop());

            $this->pila->push($a-$b);
            
        }    
    }

    public function mod(){
        if ($this->pila->length()>1){
            $b = floatval($this->pila->pop());
            $a = floatval($this->pila->pop());

            $this->pila->push($a%$b);
            
        }    
    }

    public function multiplicacion(){
        if ($this->pila->length()>1){
            $b = floatval($this->pila->pop());
            $a = floatval($this->pila->pop());

            $this->pila->push($a*$b);
            
        }    
    }
    public function division(){
        if ($this->pila->length()>1){
            $b = floatval($this->pila->pop());
            $a = floatval($this->pila->pop());

            $this->pila->push($a/$b);
            
        }    
    }
}

// Definición de una nueva sesión
if (!isset($_SESSION['rpn'])){
    $calc = new CalculadoraRPN();
    $_SESSION['rpn'] = $calc;        
}
// Interacción con todos los botones
if (count($_POST)>0)
{
    $calc = $_SESSION['rpn'];

    if (isset($_POST['c'])) $calc->onc();
    if (isset($_POST['ce'])) $calc->clearEntry();
    if (isset($_POST['cambiarSigno'])) $calc->cambiarSigno();
    if (isset($_POST['+'])) $calc->suma();
    if (isset($_POST['sqrt'])) $calc->sqrt();
    if (isset($_POST['%'])) $calc->porcentaje();
    if (isset($_POST['7'])) $calc->digitos(7);
    if (isset($_POST['8'])) $calc->digitos(8);
    if (isset($_POST['9'])) $calc->digitos(9);
    if (isset($_POST['x'])) $calc->multiplicacion();
    if (isset($_POST['div'])) $calc->division();
    if (isset($_POST['4'])) $calc->digitos(4);
    if (isset($_POST['5'])) $calc->digitos(5);
    if (isset($_POST['6'])) $calc->digitos(6);
    if (isset($_POST['-'])) $calc->resta();
    if (isset($_POST['1'])) $calc->digitos(1);
    if (isset($_POST['2'])) $calc->digitos(2);
    if (isset($_POST['3'])) $calc->digitos(3);
    if (isset($_POST['M-'])) $calc->mMenos();
    if (isset($_POST['0'])) $calc->digitos(0);
    if (isset($_POST['punto'])) $calc->punto();
    if (isset($_POST['enter'])) $calc->enter();
    if (isset($_POST['sin'])) $calc->sin();
    if (isset($_POST['cos'])) $calc->cos();
    if (isset($_POST['tan'])) $calc->tan();
    if (isset($_POST['x2'])) $calc->pow(true);
    if (isset($_POST['xy'])) $calc->pow(false);
    if (isset($_POST['log'])) $calc->log();
    if (isset($_POST['mod'])) $calc->mod();
    if (isset($_POST['inverse'])) $calc->inverse();
    if (isset($_POST['clearError'])) $calc->clearError();
    if (isset($_POST['pi'])) $calc->digitos(pi());
    if (isset($_POST['fact'])) $calc->factorial();
    if (isset($_POST['deg'])) $calc->deg();
    if (isset($_POST['hyp'])) $calc->hyp();

    $_SESSION['rpn'] = $calc;
}


?>