<?php
namespace App\Models;

use PDO;
use \App\Token;
use \App\Mail;
use \Core\View;

class User extends \Core\Model {
    public $errors = [];

    public function __construct($data = []){
        foreach($data as $key => $value){
            $this -> $key = $value;
        };
    }

    public function save(){
        $this -> validate();

        if(empty($this -> errors)){
            $password_hash = password_hash($this -> password, PASSWORD_DEFAULT);

            $token = new Token();
            $hashed_token = $token -> getHash();
            $this -> activation_token = $token -> getValue();

            $sql = 'INSERT INTO users (name, email, password_hash, activation_hash) 
                    VALUES (:name, :email, :password_hash, :activation_hash)';

            $db = static::getDB();
            $stmt = $db -> prepare($sql);

            $stmt -> bindValue(':name', $this -> name, PDO::PARAM_STR);
            $stmt -> bindValue(':email', $this -> email, PDO::PARAM_STR);
            $stmt -> bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            $stmt -> bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);

            $stmt -> execute();

            //get last user ID

            $sql = 'SELECT id FROM users ORDER BY ID DESC LIMIT 1';
            $stmt = $db -> prepare($sql);

            $stmt -> setFetchMode(PDO::FETCH_NUM);

            $stmt -> execute();

            $table = $stmt -> fetch();

            $id = $table[0];

            $sql = 'INSERT INTO expense_category (name, user_id) SELECT name, :user_id AS user_id FROM expense_category_default';

            $db = static::getDB();
            $stmt = $db -> prepare($sql);

            $stmt -> bindValue(':user_id', $id, PDO::PARAM_INT);

            $stmt -> execute();

            $sql = 'INSERT INTO income_category (name, user_id) SELECT name, :user_id AS user_id FROM income_category_default';

            $db = static::getDB();
            $stmt = $db -> prepare($sql);

            $stmt -> bindValue(':user_id', $id, PDO::PARAM_INT);

            $stmt -> execute();

            $sql = 'INSERT INTO payment (name, user_id) SELECT name, :user_id AS user_id FROM payment_default';

            $db = static::getDB();
            $stmt = $db -> prepare($sql);

            $stmt -> bindValue(':user_id', $id, PDO::PARAM_INT);

            return $stmt -> execute();
        }

        return false;
    }

    public function validate(){
        if($this -> name == ''){
            $this -> errors[] = 'Login jest wymagany';
        }

        if(filter_var($this -> email, FILTER_VALIDATE_EMAIL) === false){
            $this -> errors[] = 'Nieprawid&lstrok;owy adres email';
        }

        if(static::emailExists($this -> email, $this -> id ?? null)){
            $this -> errors[] = 'Adres email w u&zdot;yciu';
        }
        
        $this -> validatePassword();
    }

    public function validatePassword(){
        if(isset($this -> password)){
            if(strlen($this -> password) < 6){
                $this -> errors[] = 'Has&lstrok;o musi si&eogon; sk&lstrok;ada&cacute; z conajmniej 6 znak&oacute;w';
            }

            if(preg_match('/.*[a-z]+.*/i', $this -> password) == 0){
                $this -> errors[] = 'Has&lstrok;o musi zawiera&cacute; co najmniej jedn&aogon; liter&eogon;';
            }

            if(preg_match('/.*\d+.*/i', $this -> password) == 0){
                $this -> errors[] = 'Has&lstrok;o musi zawiera&cacute; co najmniej jedn&aogon; cyfr&eogon;';
            }
        }
    }

    public static function emailExists($email, $ignore_id = null){
        $user = static::findByEmail($email);

        if($user){
            if($user -> id != $ignore_id){
                return true;
            }
        }

        return false;
    }

    public static function findByEmail($email){
        $sql = 'SELECT * FROM users WHERE email = :email';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);
        $stmt -> bindParam(':email', $email, PDO::PARAM_STR);

        $stmt -> setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt -> execute();

        return $stmt -> fetch();
    }

    public static function authenticate($email, $password){
        $user = static::findByEmail($email);

        if($user && $user -> is_active){
            if(password_verify($password, $user -> password_hash)){
                return $user;
            }
        }

        return false;
    }

    public static function findByID($id){
        $sql = 'SELECT * FROM users WHERE id = :id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);
        $stmt -> bindParam(':id', $id, PDO::PARAM_INT);

        $stmt -> setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt -> execute();

        return $stmt -> fetch();
    }

    public function rememberLogin(){
        $token = new Token();
        $hashed_token = $token -> getHash();
        $this -> remember_token = $token -> getValue();

        $this -> expiry_timestamp = time() + 60 * 60 * 24 * 30;

        $sql = 'INSERT INTO remembered_logins VALUES (:token_hash, :user_id, :expires_at)';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt -> bindValue(':user_id', $this -> id, PDO::PARAM_INT);
        $stmt -> bindValue(':expires_at', date('Y-m-d H:i:s', $this -> expiry_timestamp), PDO::PARAM_STR);

        return $stmt -> execute();
    }

    public static function sendPasswordReset($email){
        $user = static::findByEmail($email);

        if($user){
            if($user -> startPasswordReset()){
                $user -> sendPasswordResetEmail();
            }
        }
    }

    protected function startPasswordReset(){
        $token = new Token();
        $hashed_token = $token -> getHash();
        $this -> password_reset_token = $token -> getValue();

        $expiry_timestamp = time() + 60 * 60 * 2;

        $sql = 'UPDATE users 
                SET password_reset_hash = :token_hash, 
                    password_reset_expires_at = :expires_at 
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt -> bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
        $stmt -> bindValue(':id', $this -> id, PDO::PARAM_INT);

        return $stmt -> execute();
    }

    protected function sendPasswordResetEmail(){
        $url = 'http://'.$_SERVER['HTTP_HOST'].'/password/reset/'.$this -> password_reset_token;

        $text = View::getTemplate('Password/reset_email.txt', ['url' => $url]);
        $html = View::getTemplate('Password/reset_email.html', ['url' => $url]);

        Mail::send($this -> email, 'Zmiana hasła', $text, $html);
    }

    public static function findByPasswordReset($token){
        $token = new Token($token);
        $hashed_token = $token -> getHash();

        $sql = 'SELECT * FROM users WHERE password_reset_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);

        $stmt -> setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt -> execute();

        $user = $stmt -> fetch();

        if($user){
            if(strtotime($user -> password_reset_expires_at) > time()){
                return $user;
            }
        }
    }

    public function resetPassword($password, $id){
        $this -> password = $password;

        $this -> validatePassword();

        if(empty($this -> errors)){
            $password_hash = password_hash($this -> password, PASSWORD_DEFAULT);

            $sql = 'UPDATE users 
                    SET password_hash = :password_hash,
                        password_reset_hash = NULL,
                        password_reset_expires_at = NULL
                    WHERE id = :id';
            
            $db = static::getDB();
            $stmt = $db -> prepare($sql);

            $stmt -> bindValue(':id', $id, PDO::PARAM_INT);
            $stmt -> bindValue(':password_hash', $password_hash, PDO::PARAM_STR);

            return $stmt -> execute();
        }

        return false;
    }

    public function sendActivationEmail(){
        $url = 'http://'.$_SERVER['HTTP_HOST'].'/signup/activate/'.$this -> activation_token;

        $text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]);
        $html = View::getTemplate('Signup/activation_email.html', ['url' => $url]);

        Mail::send($this -> email, 'Aktywacja konta', $text, $html);
    }

    public static function activate($value){
        $token = new Token($value);
        $hashed_token = $token -> getHash();

        $sql = 'UPDATE users 
                    SET is_active = 1,
                        activation_hash = NULL
                    WHERE activation_hash = :hashed_token';
            
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);

        $stmt -> execute();
    }

    public function saveIncomeCategory($id){
        $sql = 'UPDATE income_category SET name = :name WHERE id = :id AND user_id = :user_id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);        
        $stmt -> bindValue(':name', $this -> name, PDO::PARAM_STR);
        $stmt -> bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt -> execute();
    }

    public function saveExpenseCategory($id){
        $sql = 'UPDATE expense_category SET name = :name, monthly_limit = :monthly_limit WHERE id = :id AND user_id = :user_id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        if($this -> monthly_limit)
            $stmt -> bindValue(':monthly_limit', $this -> monthly_limit, PDO::PARAM_STR);
        else 
            $stmt -> bindValue(':monthly_limit', NULL, PDO::PARAM_STR);
        $stmt -> bindValue(':name', $this -> name, PDO::PARAM_STR);
        $stmt -> bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt -> execute();
    }

    public function savePaymentMethod($id){
        $sql = 'UPDATE payment SET name = :name, monthly_limit = :monthly_limit WHERE id = :id AND user_id = :user_id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        if($this -> monthly_limit)
            $stmt -> bindValue(':monthly_limit', $this -> monthly_limit, PDO::PARAM_STR);
        else 
            $stmt -> bindValue(':monthly_limit', NULL, PDO::PARAM_STR);
        $stmt -> bindValue(':name', $this -> name, PDO::PARAM_STR);
        $stmt -> bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt -> execute();
    }

    public function addExpenseCategory(){
        $sql = 'INSERT INTO expense_category (name, user_id, monthly_limit) VALUES (:name, :user_id, :monthly_limit)';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        if($this -> monthly_limit)
            $stmt -> bindValue(':monthly_limit', $this -> monthly_limit, PDO::PARAM_STR);
        else 
            $stmt -> bindValue(':monthly_limit', NULL, PDO::PARAM_STR);
        $stmt -> bindValue(':name', $this -> name, PDO::PARAM_STR);

        return $stmt -> execute();
    }

    public function addIncomeCategory(){
        $sql = 'INSERT INTO income_category (name, user_id) VALUES (:name, :user_id)';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);        
        $stmt -> bindValue(':name', $this -> name, PDO::PARAM_STR);

        return $stmt -> execute();
    }

    public function addPaymentMethod(){
        $sql = 'INSERT INTO payment (name, user_id, monthly_limit) VALUES (:name, :user_id, :monthly_limit)';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        if($this -> monthly_limit)
            $stmt -> bindValue(':monthly_limit', $this -> monthly_limit, PDO::PARAM_STR);
        else 
            $stmt -> bindValue(':monthly_limit', NULL, PDO::PARAM_STR);
        $stmt -> bindValue(':name', $this -> name, PDO::PARAM_STR);

        return $stmt -> execute();
    }

    public function deleteIncomeCategory($id){
        $sql = 'DELETE FROM income_category WHERE id = :id AND user_id = :user_id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt -> bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt -> execute();
    }

    public function deleteExpenseCategory($id){
        $sql = 'DELETE FROM expense_category WHERE id = :id AND user_id = :user_id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt -> bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt -> execute();
    }

    public function deletePaymentMethod($id){
        $sql = 'DELETE FROM payment WHERE id = :id AND user_id = :user_id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);        
        $stmt -> bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt -> execute();
    }

    public function deleteBalance(){
        if(($this -> authenticate($this -> email, $this -> password)) -> id == $_SESSION['user_id']){
            if($this -> deleteExpenses() && $this -> deleteIncomes())
                return true;
        } else return false;
    }

    protected function deleteExpenses(){
        $sql = 'DELETE FROM expenses WHERE user_id = :user_id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        return $stmt -> execute();
    }

    protected function deleteIncomes(){
        $sql = 'DELETE FROM incomes WHERE user_id = :user_id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        return $stmt -> execute();
    }

    public function deleteAccount(){
        if(($this -> authenticate($this -> email, $this -> password)) -> id == $_SESSION['user_id']){
            if($this -> deleteExpenses() && $this -> deleteIncomes() && $this -> deleteAllIncomeCategories() && $this -> deleteAllExpenseCategories() 
            && $this -> deleteAllPaymentMethods())
                $sql = 'DELETE FROM users WHERE id = :user_id';

                $db = static::getDB();
                $stmt = $db -> prepare($sql);
        
                $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        
                return $stmt -> execute();
        } else return false;
    }

    protected function deleteAllIncomeCategories(){
        $sql = 'DELETE FROM income_category WHERE user_id = :user_id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        return $stmt -> execute();
    }

    protected function deleteAllExpenseCategories(){
        $sql = 'DELETE FROM expense_category WHERE user_id = :user_id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        return $stmt -> execute();
    }

    protected function deleteAllPaymentMethods(){
        $sql = 'DELETE FROM payment WHERE user_id = :user_id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        return $stmt -> execute();
    }

    public function changeEmail(){
        $user = $this -> findByID($_SESSION['user_id']);

        if(password_verify($this -> password, $user -> password_hash)){
            if(!$this -> emailExists($this -> email)){
                $token = new Token();
                $hashed_token = $token -> getHash();
                $this -> activation_token = $token -> getValue();
                
                $sql = 'UPDATE users SET email = :email, activation_hash = :activation_hash WHERE id = :id';
                
                $db = static::getDB();
                $stmt = $db -> prepare($sql);
                
                $stmt -> bindValue(':email', $this -> email, PDO::PARAM_STR);
                $stmt -> bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt -> bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);

                return $stmt -> execute();
            }
        }
    }

    public function changePassword(){
        $user = $this -> findByID($_SESSION['user_id']);

        if(static::authenticate($user -> email, $this -> oldPassword)){
            $this -> resetPassword($this -> password, $user -> id);

            return true;
        } else return false;
    }

    public static function getExpenseLimits(){
        $sql = 'SELECT name, monthly_limit FROM expense_category WHERE user_id = :user_id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);
        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt -> execute();

        return $stmt -> fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPaymentLimits(){
        $sql = 'SELECT name, monthly_limit FROM payment WHERE user_id = :user_id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);
        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt -> execute();

        return $stmt -> fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUserExpenseCategories(){
        $sql = 'SELECT * FROM expense_category WHERE user_id = :user_id';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt -> execute();
        
        return $stmt -> fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUserIncomeCategories(){
        $sql = 'SELECT * FROM income_category WHERE user_id = :user_id';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt -> execute();
        
        return $stmt -> fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUserPaymentMethods(){
        $sql = 'SELECT * FROM payment WHERE user_id = :user_id';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt -> execute();
        
        return $stmt -> fetchAll(PDO::FETCH_ASSOC);
    }
}