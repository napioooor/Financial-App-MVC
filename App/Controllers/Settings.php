<?php
namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\Models\Balance;
use \App\Models\User;

class Settings extends Authenticated {
    protected function before(){
        parent::before();
        
        $this -> user = Auth::getUser();
    }

    public function showAction(){
        View::renderTemplate('Settings/show.html', [
            'user' => $this -> user,
            'user_expense_categories' => Balance::getUserExpenseCategories(),
            'payments' => Balance::getUserPaymentMethods(),
            'user_income_categories' => Balance::getUserIncomeCategories()
        ]);
    }
/*
    public function editAction(){
        View::renderTemplate('Settings/edit.html', [
            'user' => $this -> user
        ]);
    }

    public function updateAction(){
        if($this -> user -> updateProfile($_POST)){
            Flash::addMessage('Zapisano zmiany');

            $this -> redirect('/Settings/show');
        } else {
            View::renderTemplate('Settings/edit.html', [
                'user' => $this -> user
            ]);
        }
    }
*/
    public function editExpCatAction(){
        $category = new User($_POST);

        if($category -> saveExpenseCategory($this -> route_params['id'])){
            Flash::addMessage('Kategoria wydatku edytowana pomyślnie!');

            $this -> redirect('/settings/show');
        } else {
            Flash::addMessage('Coś poszło nie tak!', Flash::WARNING);

            $this -> redirect('/settings/show');
        }
    }

    public function editIncCatAction(){
        $category = new User($_POST);

        if($category -> saveIncomeCategory($this -> route_params['id'])){
            Flash::addMessage('Kategoria przychodu edytowana pomyślnie!');

            $this -> redirect('/settings/show');
        } else {
            Flash::addMessage('Coś poszło nie tak!', Flash::WARNING);

            $this -> redirect('/settings/show');
        }
    }

    public function editPayMetAction(){
        $method = new User($_POST);

        if($method -> savePaymentMethod($this -> route_params['id'])){
            Flash::addMessage('Metoda płatności edytowana pomyślnie!');

            $this -> redirect('/settings/show');
        } else {
            Flash::addMessage('Coś poszło nie tak!', Flash::WARNING);

            $this -> redirect('/settings/show');
        }
    }

    public function addIncomeCategoryAction(){
        $category = new User($_POST);

        if($category -> addIncomeCategory()){
            Flash::addMessage('Kategoria wydatku dodana pomyślnie!');

            $this -> redirect('/settings/show');
        } else {
            Flash::addMessage('Coś poszło nie tak!', Flash::WARNING);

            $this -> redirect('/settings/show');
        }
    }

    public function addExpenseCategoryAction(){
        $category = new User($_POST);

        if($category -> addExpenseCategory()){
            Flash::addMessage('Metoda płatności dodana pomyślnie!');

            $this -> redirect('/settings/show');
        } else {
            Flash::addMessage('Coś poszło nie tak!', Flash::WARNING);

            $this -> redirect('/settings/show');
        }
    }

    public function addPaymentMethodAction(){
        $method = new User($_POST);

        if($method -> addPaymentMethod()){
            Flash::addMessage('Metoda płatności dodana pomyślnie!');

            $this -> redirect('/settings/show');
        } else {
            Flash::addMessage('Coś poszło nie tak!', Flash::WARNING);

            $this -> redirect('/settings/show');
        }
    }

    public function deleteExpenseCatAction(){
        $category = new User($_POST);

        if($category -> deleteExpenseCategory($this -> route_params['id'])){
            Flash::addMessage('Kategoria wydatku usunięta pomyślnie!');

            $this -> redirect('/settings/show');
        } else {
            Flash::addMessage('Coś poszło nie tak!', Flash::WARNING);

            $this -> redirect('/settings/show');
        }
    }

    public function deleteIncomeCatAction(){
        $category = new User($_POST);

        if($category -> deleteIncomeCategory($this -> route_params['id'])){
            Flash::addMessage('Kategoria przychodu usunięta pomyślnie!');

            $this -> redirect('/settings/show');
        } else {
            Flash::addMessage('Coś poszło nie tak!', Flash::WARNING);

            $this -> redirect('/settings/show');
        }
    }

    public function deletePaymentMetAction(){
        $method = new User($_POST);

        if($method -> deletePaymentMethod($this -> route_params['id'])){
            Flash::addMessage('Metoda płatności usunięta pomyślnie!');

            $this -> redirect('/settings/show');
        } else {
            Flash::addMessage('Coś poszło nie tak!', Flash::WARNING);

            $this -> redirect('/settings/show');
        }
    }

    public function deleteBalanceAction(){
        $method = new User($_POST);

        if($method -> deleteBalance()){
            Flash::addMessage('Wydatki i przychody zostały usunięte pomyślnie!');

            $this -> redirect('/settings/show');
        } else {
            Flash::addMessage('Coś poszło nie tak!', Flash::WARNING);

            $this -> redirect('/settings/show');
        }
    }

    public function deleteAccountAction(){
        $method = new User($_POST);

        if($method -> deleteAccount()){
            Flash::addMessage('Konto zostało usunięte pomyślnie.');

            $this -> redirect('/');
        } else {
            Flash::addMessage('Coś poszło nie tak!', Flash::WARNING);

            $this -> redirect('/settings/show');
        }
    }
}