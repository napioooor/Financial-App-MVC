<?php
namespace App\Controllers;

use \Core\View;
use \App\Dates;
use \App\Models\Balance;
use \App\Flash;
use \App\Auth;

class Items extends Authenticated {
    protected function before(){
        parent::before();
        
        $this -> user = Auth::getUser();
    }

    public function incomeAction(){
        View::renderTemplate('Items/income.html', [
            'todays_date' => Dates::getTodaysDate(), 
            'user_incomes' => Balance::getUserIncomeCategories()
        ]);
    }

    public function addIncomeAction(){
        $income = new Balance($_POST);

        if($income -> saveIncome()){
            Flash::addMessage('Przychód dodany pomyślnie!');

            $this -> redirect('/');
        } else {
            Flash::addMessage('Coś poszło nie tak!', Flash::WARNING);

            $this -> redirect('/');
        }
    }

    public function expenseAction(){
        View::renderTemplate('Items/expense.html', [
            'todays_date' => Dates::getTodaysDate(), 
            'user_expenses' => Balance::getUserExpenseCategories(),
            'payments' => Balance::getUserPaymentMethods()
        ]);
    }

    public function addExpenseAction(){
        $expense = new Balance($_POST);

        if($expense -> saveExpense()){
            Flash::addMessage('Wydatek dodany pomyślnie!');

            $this -> redirect('/');
        } else {
            Flash::addMessage('Coś poszło nie tak!', Flash::WARNING);

            $this -> redirect('/');
        }
    }

    public function browseAction(){
        if(isset($_POST["period"])){
            if($_POST["period"] == 1){
                $date1 = date("Y-m-01");
                $date2 = date('Y-m-01', strtotime("+1 months", strtotime($date1)));
            } else if($_POST["period"] == 2){
                $date2 = date("Y-m-01");
                $date1 = date('Y-m-01', strtotime("-1 months", strtotime($date2)));
            } else if($_POST["period"] == 3){
                $date1 = date("Y-01-01");
                $date2 = date("Y-m-d");
            } else if($_POST["period"] == 4){
                if($_POST['date1'] < $_POST['date2']){
                    $date1 = $_POST['date1'];
                    $date2 = $_POST['date2'];
                } else {
                    $date1 = $_POST['date2'];
                    $date2 = $_POST['date1'];
                }
            }
            View::renderTemplate('Items/browse.html', [
                'user_expenses' => Balance::getUserExpenses($date1, $date2),
                'user_incomes' => Balance::getUserIncomes($date1, $date2),
                'expense_sum' => $this -> getExpenseData($date1, $date2),
                'income_sum' => $this -> getIncomeData($date1, $date2),
                'selected' => $_POST["period"],
                'date1' => $date1,
                'date2' => $date2,
                'sum' => $this -> getSum($date1, $date2)
            ]);
        } else View::renderTemplate('Items/browse.html');        
    }

    protected function getSum($date1, $date2){
        $incomes = Balance::getIncomeSums($date1, $date2);
        $expenses = Balance::getExpenseSums($date1, $date2);
        $sum = 0;

        foreach($incomes as $income){
            $sum += $income['SUM(amount)'];
        }

        foreach($expenses as $expense){
            $sum -= $expense['SUM(amount)'];
        }

        return $sum;
    }

    protected function getIncomeData($date1, $date2){
        $data = [];
        $data[] = ['Kategoria', 'Kwota'];

        $incomes = Balance::getIncomeSums($date1, $date2);

        foreach($incomes as $income){
            $data[] = [$income['category'], (float) $income['SUM(amount)']];
        }

        return $data;
    }

    protected function getExpenseData($date1, $date2){
        $data = [];
        $data[] = ['Kategoria', 'Kwota'];

        $expenses = Balance::getExpenseSums($date1, $date2);

        foreach($expenses as $expense){
            $data[] = [$expense['category'], (float) $expense['SUM(amount)']];
        }

        return $data;
    }
}