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
}