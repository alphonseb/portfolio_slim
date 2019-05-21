<?php

//Home
$app
    ->get(
        '/',
        function($request, $response){
            $viewData = [];
            
            $viewData['meta_description'] = 'Update Later';
            
            $viewData['name'] = 'Alphonse Bouy';
            $viewData['function'] = 'Developer';
            $viewData['description'] = "Blabla I'm a developer I do lots of things let's work together !";
            
            $query = $this->db->query('SELECT * from options');
            $options = $query->fetchAll();
            
            $viewData['options'] = $options;
            
            return $this->view->render($response, 'pages/home.twig', $viewData);
        }
    )
    ->setName('home')
;

//Projects List
$app
    ->get(
        '/projects',
        function ($request, $response) {
            //Fetch cqtegories
            $query = $this->db->query('SELECT * FROM project_categories');
            $project_categories = $query->fetchAll();
            
            
            $viewData = [];
            $viewData['project_categories'] = $project_categories;
            
            return $this->view->render($response, 'pages/projects.twig', $viewData);
        }
    )
    ->setName('projects')
;
    
//Single Project
$app
    ->get(
        '/projects/{slug: [a-z_-]',
        function ($request, $response, $arguments) {
            $viewData = [];
            return $this->view->render($response, 'pages/project.twig', $viewData);
        }
    )
    ->setName('project')
;
    
//Contact
$app
    ->get(
        '/contact',
        function ($request, $response) {
            $viewData = [];
            $viewData['name'] = 'Alphonse Bouy';
            return $this->view->render($response, 'pages/contact.twig', $viewData);
        }
    )
    ->setName('contact')
;
    
//Send Message
$app
    ->post(
        '/contact',
        function ($request, $response) {
            $viewData = [];
            $viewData['errors'] = [];
            $viewData['success'] = [];
            
            if (!empty($_POST)) {
                if (!empty($_POST['name'])) {
                    $name = trim($_POST['name']);
                    $viewData['form_name'] = $name;
                } else {
                    $viewData['errors']['name'] = 'I need your name !';
                }
                if (!empty($_POST['email'])) {
                    $email = trim($_POST['email']);
                    $viewData['email'] = $email;
                } else {
                    $viewData['errors']['email'] = 'I need your email !';
                }
                if (!empty($_POST['message'])) {
                    $message = trim($_POST['message']);
                    $viewData['message'] = $message;
                } else {
                    $viewData['errors']['message'] = 'Surely you have something to say.';
                }
                
                if (empty($viewData['errors'])) {
                    $prepare = $this->db->prepare('
                        INSERT INTO
                            messages(name, email, message)
                        VALUES
                            (:name, :email, :message)
                    ');
                    
                    $prepare->bindValue('name', $name);
                    $prepare->bindValue('email', $email);
                    $prepare->bindValue('message', $message);
                    
                    if($prepare->execute()){
                        $viewData['success']['sent'] = "Thank you for your message, I'll get back to you as quick as possible !";
                        unset($viewData['form_name']);
                        unset($viewData['email']);
                        unset($viewData['message']);
                    }
                }
            }

            $viewData['name'] = 'Alphonse Bouy';
            return $this->view->render($response, 'pages/contact.twig', $viewData);
        }
    )
;
    
//Admin
$app
    ->get(
        '/admin',
        function ($request, $response) {
            
            if (isset($_SESSION['user'])) {
                $viewData = [];
                $viewData['user'] = $_SESSION['user'];
                
                return $this->view->render($response, 'pages/admin.twig',$viewData);
            }
            
            return $response->withRedirect('admin/login');
        }
    )
    ->setName('admin')
;

//Login
$app
    ->get(
        '/admin/login',
        function ($request, $response) {
            return $this->view->render($response, 'pages/login.twig');
        }
    )
    ->setName('login')
;

//Login Post
$app
    ->post(
        '/admin/login',
        function ($request, $response) {
            $viewData = [];
            $viewData['errors'] = [];
            if (!empty($_POST)) {
                
                
                if (!empty($_POST['login'])) {
                    $login = trim($_POST['login']);
                }
                else{
                    $viewData['errors']['login'] = 'Missing Login';
                }
                if (!empty($_POST['password'])) {
                    $password = $_POST['password'];
                }
                else {
                    $viewData['errors']['password'] = 'Missing Password';
                }
                
                if (empty($viewData['errors'])) {
                    $prepare = $this->db->prepare(
                        '
                        SELECT *
                        FROM users
                        WHERE login = :login
                    '
                    );
                    $prepare->bindValue('login', $_POST['login']);
                    $prepare->execute();

                    $user = $prepare->fetch();

                    if ($user && password_verify($_POST['password'], $user->password)) {
                        unset($user->password);
                        $_SESSION['user'] = $user;
                        return $response->withRedirect('../admin');
                    }
                    else {
                        $viewData['errors']['combination'] = 'Incorrect Password/Login Combination';
                    }
                }
                
                return $this->view->render($response, 'pages/login.twig', $viewData);
                
            }
        }
    )
;

//Disconnect
$app
    ->get(
        '/admin/disconnect',
        function ($request, $response) {
            unset($_SESSION['user']);
            return $response->withRedirect('login');
        }
    )
    ->setName('disconnect')
;