<?php
namespace phpsgex\framework\interfaces;

interface ILoginBridge{
    public function Login($username, $password);
    public function Register($username, $email, $password,  $_race, $_lang= "en", $_cityName= "", $initPos= NULL);
    public function Logout();
    //public function GetUser();
}