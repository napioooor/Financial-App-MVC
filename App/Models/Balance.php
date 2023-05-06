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

    public static function getUserExpenses($date1, $date2){
        $sql = 'SELECT * FROM expenses WHERE user_id = :user_id AND 
            DATE(date) BETWEEN DATE(:date1) AND DATE(:date2) ORDER BY date DESC';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt -> bindValue(':date1', $date1, PDO::PARAM_STR);
        $stmt -> bindValue(':date2', $date2, PDO::PARAM_STR);

        $stmt -> execute();
        
        return $stmt -> fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUserIncomes($date1, $date2){
        $sql = 'SELECT * FROM incomes WHERE user_id = :user_id AND 
            DATE(date) BETWEEN DATE(:date1) AND DATE(:date2) ORDER BY date DESC';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt -> bindValue(':date1', $date1, PDO::PARAM_STR);
        $stmt -> bindValue(':date2', $date2, PDO::PARAM_STR);

        $stmt -> execute();
        
        return $stmt -> fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getIncomeSums($date1, $date2){
        $sql = 'SELECT DISTINCT category, SUM(amount) FROM incomes 
            WHERE user_id = :user_id AND DATE(date) BETWEEN DATE(:date1) AND DATE(:date2) GROUP BY category ORDER BY SUM(amount) DESC';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt -> bindValue(':date1', $date1, PDO::PARAM_STR);
        $stmt -> bindValue(':date2', $date2, PDO::PARAM_STR);

        $stmt -> execute();
        
        return $stmt -> fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getExpenseSums($date1, $date2){
        $sql = 'SELECT DISTINCT category, SUM(amount) FROM expenses 
            WHERE user_id = :user_id AND DATE(date) BETWEEN DATE(:date1) AND DATE(:date2) GROUP BY category ORDER BY SUM(amount) DESC';
        
        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt -> bindValue(':date1', $date1, PDO::PARAM_STR);
        $stmt -> bindValue(':date2', $date2, PDO::PARAM_STR);

        $stmt -> execute();
        
        return $stmt -> fetchAll(PDO::FETCH_ASSOC);
    }
}