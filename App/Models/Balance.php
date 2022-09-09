<?php
namespace App\Models;

use PDO;

class Balance extends \Core\Model {
    public function __construct($data = []){
        foreach($data as $key => $value){
            $this -> $key = $value;
        };
    }

    public function saveIncome(){
        $sql = 'INSERT INTO incomes (user_id, amount, category, date, comment) 
        VALUES (:user_id, :amount, :category, :date, :comment)';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt -> bindValue(':amount', $this -> amount, PDO::PARAM_STR);
        $stmt -> bindValue(':category', $this -> category, PDO::PARAM_STR);
        $stmt -> bindValue(':date', $this -> date, PDO::PARAM_STR);
        $stmt -> bindValue(':comment', $this -> comment, PDO::PARAM_STR);

        return $stmt -> execute();
    }

    public static function getUserIncomeCategories(){
        $sql = 'SELECT * FROM income_category WHERE user_id = :user_id';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt -> execute();
        
        return $stmt -> fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveExpense(){
        $sql = 'INSERT INTO expenses (user_id, amount, category, date, payment, comment) 
        VALUES (:user_id, :amount, :category, :date, :payment, :comment)';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt -> bindValue(':amount', $this -> amount, PDO::PARAM_STR);
        $stmt -> bindValue(':category', $this -> category, PDO::PARAM_STR);
        $stmt -> bindValue(':date', $this -> date, PDO::PARAM_STR);
        $stmt -> bindValue(':payment', $this -> payment, PDO::PARAM_STR);
        $stmt -> bindValue(':comment', $this -> comment, PDO::PARAM_STR);

        return $stmt -> execute();
    }

    public static function getUserExpenseCategories(){
        $sql = 'SELECT * FROM expense_category WHERE user_id = :user_id';
        
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