<?php
use Slim\Exception\NotFoundException;

//Home
$app
    ->get(
        '/',
        function($request, $response){
            $viewData = [];
            if (isset($_SESSION['user'])) {
                $viewData['user'] = $_SESSION['user'];
            }
            
            $viewData['meta_description'] = 'Update Later';
            
            $viewData['name'] = 'Alphonse Bouy';
            $viewData['function'] = 'Developer';
            $viewData['description'] = "Hi I'm Alphonse, I'm a front-end developer whith a special interest in UX-Design. <br> I love crafting neat user-focused experiences. I also do a lot of Wordpress Theme Development. <br> Hit me up if you want to work together on something great !";
            
            $query = $this->db->query('SELECT * from options');
            $options = $query->fetchAll();
            
            $viewData['options'] = $options;
            
            $query = $this->db->query('SELECT * from projects ORDER BY RAND() LIMIT 3');
            $projects = $query->fetchAll();
            $viewData['projects'] = $projects;
            
            return $this->view->render($response, 'pages/home.twig', $viewData);
        }
    )
    ->setName('home')
;

$app
    ->post(
        '/projects',
        function($request, $response){
            $cat_ids = json_decode($_POST['values']);
            $projects = [];
            if (empty($cat_ids)) {
                $query = $this->db->query(
                    'SELECT * from projects'
                );
                $projects = $query->fetchAll();
            }
            else{
                $marqueurs = array_fill(0, count($cat_ids), '?');
                $prepare = $this->db->prepare(
                    'SELECT * FROM projects WHERE category_id IN ('.implode(',', $marqueurs).')'
                );
                $prepare->execute($cat_ids);
                $projects = $prepare->fetchAll();
            }
            
            $viewData = [];
            $query = $this->db->query('SELECT * from options');
            $options = $query->fetchAll();

            $viewData['options'] = $options;
            if (isset($_SESSION['user'])) {
                $viewData['user'] = $_SESSION['user'];
            }
            $viewData['projects'] = $projects;
            
            return $this->view->render($response, 'partials/projects.twig', $viewData);
        }
    )
    ;

//Projects List
$app
->get(
        '/projects',
        function ($request, $response) {
            //Fetch cqtegories
            $query = $this->db->query('SELECT * FROM project_categories');
            $project_categories = $query->fetchAll();
            
            $query = $this->db->query('SELECT * FROM projects');
            $projects = $query->fetchAll();
            
            $viewData = [];
            if (isset($_SESSION['user'])) {
                $viewData['user'] = $_SESSION['user'];
            }
            $query = $this->db->query('SELECT * from options');
            $options = $query->fetchAll();

            $viewData['options'] = $options;
            $viewData['project_categories'] = $project_categories;
            $viewData['projects'] = $projects;
            
            return $this->view->render($response, 'pages/projects.twig', $viewData);
        }
    )
    ->setName('projects')
;
    
//Single Project
$app
    ->get(
        '/projects/{slug: [0-9a-z_-]+}',
        function ($request, $response, $arguments) {
            
            //project
            $prepare = $this->db->prepare(
                'SELECT * FROM projects WHERE slug = :slug LIMIT 1'
            );
            $prepare->bindValue('slug', $arguments['slug']);
            $prepare->execute();
            $project = $prepare->fetch();

            if (!$project) {
                throw new NotFoundException($request, $response);
            }
            
            //category
            $prepare = $this->db->prepare(
                'SELECT * FROM project_categories WHERE id = :id LIMIT 1'
            );
            $prepare->bindValue('id', $project->category_id);
            $prepare->execute();
            $category = $prepare->fetch();
            
            //next project
            $prepare = $this->db->prepare(
                'SELECT * FROM projects WHERE NOT id = :id ORDER BY RAND() LIMIT 1'
            );
            $prepare->bindValue('id', $project->id);
            $prepare->execute();
            $next_project = $prepare->fetch();
            
            
            $viewData = [];
            if (isset($_SESSION['user'])) {
                $viewData['user'] = $_SESSION['user'];
            }
            $query = $this->db->query('SELECT * from options');
            $options = $query->fetchAll();

            $viewData['options'] = $options;
            $viewData['project'] = $project;
            $viewData['category'] = $category;
            $viewData['next_project'] = $next_project;
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
            if (isset($_SESSION['user'])) {
                $viewData['user'] = $_SESSION['user'];
            }
            $query = $this->db->query('SELECT * from options');
            $options = $query->fetchAll();

            $viewData['options'] = $options;
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
            if (isset($_SESSION['user'])) {
                $viewData['user'] = $_SESSION['user'];
            }
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
            $query = $this->db->query('SELECT * from options');
            $options = $query->fetchAll();

            $viewData['options'] = $options;
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
            
            if (empty($_SESSION['user'])) {
                return $response->withRedirect('admin/login');
            }
            
            $viewData = [];
            $viewData['user'] = $_SESSION['user'];
            
            $query = $this->db->query('SELECT * FROM projects');
            $projects = $query->fetchAll();
            
            $viewData['projects'] = $projects;
            
            $query = $this->db->query('SELECT * FROM messages ORDER BY id DESC');
            $messages = $query->fetchAll();
            
            $viewData['messages'] = $messages;
            
            $query = $this->db->query('SELECT * FROM options');
            $options = $query->fetchAll();
            
            $viewData['options'] = $options;

            $viewData['user'] = $_SESSION['user'];
            
            return $this->view->render($response, 'pages/admin.twig',$viewData);
            
        }
    )
    ->setName('admin')
;

$app
    ->post(
        '/admin',
        function ($request, $response) {
            if (!empty($_POST['project_id'])) {
                $prepare = $this->db->prepare(
                    'DELETE FROM projects WHERE id = :id'
                );
                $prepare->bindValue('id', $_POST['project_id']);
                if($prepare->execute()){
                    die(json_encode(['res' => true]));
                }
            }
            
            $twitterLink = trim($_POST['twitter']);
            $linkedinLink = trim($_POST['linkedin']);
            $githubLink = trim($_POST['github']);
            
            $prepare = $this->db->prepare(
                'UPDATE options SET link = :link WHERE name = "twitter"'
            );
            $prepare->bindValue('link', $twitterLink);
            $prepare->execute();
            
            $prepare = $this->db->prepare(
                'UPDATE options SET link = :link WHERE name = "linkedin"'
            );
            $prepare->bindValue('link', $linkedinLink);
            $prepare->execute();
            
            $prepare = $this->db->prepare(
                'UPDATE options SET link = :link WHERE name = "github"'
            );
            $prepare->bindValue('link', $githubLink);
            $prepare->execute();
            
            $url = $this->router->pathFor('admin');
            
            return $response->withRedirect($url);
        }
    )
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

//Add project
$app
    ->get(
        '/admin/add-project',
        function ($request, $response) {
            if (empty($_SESSION['user'])) {
                return $response->withRedirect('../admin/login');
            }
            
            $viewData = [];
            if (isset($_SESSION['user'])) {
                $viewData['user'] = $_SESSION['user'];
            }

            $query = $this->db->query('SELECT * FROM project_categories');
            $categories = $query->fetchAll();


            $viewData['categories'] = $categories;
            
            return $this->view->render($response, 'pages/add-project.twig', $viewData);
        }
    )
    ->setName('add-project')
;

$app
    ->post(
        '/admin/add-project',
        function ($request, $response) {
            
            
            
            $viewData = [];
            $viewData['errors'] = [];
            if (isset($_SESSION['user'])) {
                $viewData['user'] = $_SESSION['user'];
            }

            $query = $this->db->query('SELECT * FROM project_categories');
            $categories = $query->fetchAll();


            $viewData['categories'] = $categories;
            
            $slug = '';
            $title = '';
            $content = '';
            $category_id = 0;
            $thumbnail_url = '';
            $link_online = '';
            $link_repo = '';
            
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
                /**
                 * Handle file upload
                 * Credit : http: //php.net/manual/fr/features.file-upload.php#114004
                 */
                try {

                    // Undefined | Multiple Files | $_FILES Corruption Attack
                    // If this request falls under any of them, treat it invalid.
                    if (
                        !isset($_FILES['thumbnail']['error']) ||
                        is_array($_FILES['thumbnail']['error'])
                    ) {
                        throw new RuntimeException('Paramètres invalides');
                    }

                    // Check $_FILES['thumbnail']['error'] value.
                    switch ($_FILES['thumbnail']['error']) {
                        case UPLOAD_ERR_OK:
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            throw new RuntimeException('Aucun fichier');
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            throw new RuntimeException('Fichier trop volumineux');
                        default:
                            throw new RuntimeException('Erreurs inconnues');
                    }

                    // You should also check filesize here.
                    if ($_FILES['thumbnail']['size'] > 2000000) {
                        throw new RuntimeException('Fichier trop volumineux');
                    }

                    // DO NOT TRUST $_FILES['thumbnail']['mime'] VALUE !!
                    // Check MIME Type by yourself.
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    if (false === $ext = array_search(
                        $finfo->file($_FILES['thumbnail']['tmp_name']),
                        array(
                            'jpg' => 'image/jpeg',
                            'png' => 'image/png',
                            'gif' => 'image/gif',
                        ),
                        true
                    )) {
                        throw new RuntimeException('Format de fichier invalide');
                    }

                    // You should name it uniquely.
                    // DO NOT USE $_FILES['thumbnail']['name'] WITHOUT ANY VALIDATION !!
                    // On this example, obtain safe unique name from its binary data.
                    if (!move_uploaded_file(
                        $_FILES['thumbnail']['tmp_name'],
                        sprintf(
                            'uploads/%s.%s',
                            $name = sha1_file($_FILES['thumbnail']['tmp_name']),
                            $ext
                        )
                    )) {
                        throw new RuntimeException('Impossible d\'enregistrer le fichier');
                    }

                    $thumbnail_url = sprintf(
                        'uploads/%s.%s',
                        $name,
                        $ext
                    );
                } catch (RuntimeException $e) {

                    $viewData['errors']['file'] = $e->getMessage();
                }
            }

            if (!empty($_POST['title'])) {
                $title = trim($_POST['title']);
                $viewData['title'] = $title;
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            } 
            else {
                $viewData['errors']['title'] = 'Besoin d\'un titre';
            }
            if (!empty($_POST['content'])) {
                $viewData['content'] = $content;
                $content = $_POST['content'];
            } 
            else {
                $viewData['errors']['content'] = 'Besoin de contenu';
            }
            if (!empty($_POST['link_online'])) {
                $link_online = $_POST['link_online'];
                $viewData['link_online'] = $link_online;
            }
            if (!empty($_POST['link_repo'])) {
                $link_repo = $_POST['link_repo'];
                $viewData['link_repo'] = $link_repo;
            }

            if (!empty($_POST['category'])) {
                if ($_POST['category'] != 'default') {
                    $category_id = $_POST['category'];
                } elseif (!empty($_POST['category_name'])) {
                    $prepare = $this->db->prepare(
                        'INSERT INTO project_categories (name)
                         VALUES (:name)'
                    );
                    $prepare->bindValue('name', $_POST['category_name']);
                    $prepare->execute();
                    $category_id = $this->db->lastInsertId();
                }
            }
            
            if (empty($viewData['errors'])) {
                $prepare = $this->db->prepare( '
                    INSERT INTO
                        projects(slug, title, content, category_id, thumbnail_url, link_online, link_repo)
                    VALUES
                        (:slug, :title, :content, :category_id, :thumbnail_url, :link_online, :link_repo)
                ');
                
                $prepare->bindValue(':slug', $slug);
                $prepare->bindValue(':title', $title);
                $prepare->bindValue(':content', $content);
                $prepare->bindValue(':category_id', $category_id);
                $prepare->bindValue(':thumbnail_url', $thumbnail_url);
                $prepare->bindValue(':link_online', $link_online);
                $prepare->bindValue(':link_repo', $link_repo);
                
                if ($prepare->execute()) {
                    return $response->withRedirect('../admin');
                }
            }
            
            return $this->view->render($response, 'pages/add-project.twig', $viewData);
        }
    )
;

//Edit project
$app
    ->get(
        '/admin/edit-project/{slug: [0-9a-z_-]+}',
        function($request, $response, $arguments){

            if (empty($_SESSION['user'])) {
                return $response->withRedirect('../admin/login');
            }
            
            $prepare = $this->db->prepare(
                'SELECT * FROM projects WHERE slug = :slug LIMIT 1'
            );
            $prepare->bindValue('slug', $arguments['slug']);
            $prepare->execute();
            $project = $prepare->fetch();

            if (!$project) {
                throw new NotFoundException($request, $response);
            }
            
            $query = $this->db->query('SELECT * from project_categories');
            $categories = $query->fetchAll();
            
            $viewData = [];
            if (isset($_SESSION['user'])) {
                $viewData['user'] = $_SESSION['user'];
            }
            $viewData['project'] = $project;
            $viewData['categories'] = $categories;
            
            return $this->view->render($response, 'pages/edit-project.twig', $viewData);
            
        }
    )
    ->setName('edit-project')
;

$app
    ->post(
        '/admin/edit-project/{slug: [0-9a-z_-]+}',
        function ($request, $response, $arguments) {

            $viewData = [];
            $viewData['errors'] = [];
            if (isset($_SESSION['user'])) {
                $viewData['user'] = $_SESSION['user'];
            }

            $query = $this->db->query('SELECT * FROM project_categories');
            $categories = $query->fetchAll();


            $viewData['categories'] = $categories;
            
            
            $prepare = $this->db->prepare(
                'SELECT * FROM projects WHERE slug = :slug LIMIT 1'
            );
            $prepare->bindValue('slug', $arguments['slug']);
            $prepare->execute();
            $project = $prepare->fetch();
            $viewData['project'] = $project;

            $slug = $project->slug;
            $title = $project->title;
            $content = $project->content;
            $category_id = $project->category_id;
            $thumbnail_url = $project->thumbnail_url;
            $link_online = $project->link_online;
            $link_repo = $project->link_repo;


            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
                /**
                 * Handle file upload
                 * Credit : http: //php.net/manual/fr/features.file-upload.php#114004
                 */
                try {

                    // Undefined | Multiple Files | $_FILES Corruption Attack
                    // If this request falls under any of them, treat it invalid.
                    if (
                        !isset($_FILES['thumbnail']['error']) ||
                        is_array($_FILES['thumbnail']['error'])
                    ) {
                        throw new RuntimeException('Paramètres invalides');
                    }

                    // Check $_FILES['thumbnail']['error'] value.
                    switch ($_FILES['thumbnail']['error']) {
                        case UPLOAD_ERR_OK:
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            throw new RuntimeException('Aucun fichier');
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            throw new RuntimeException('Fichier trop volumineux');
                        default:
                            throw new RuntimeException('Erreurs inconnues');
                    }

                    // You should also check filesize here.
                    if ($_FILES['thumbnail']['size'] > 2000000) {
                        throw new RuntimeException('Fichier trop volumineux');
                    }

                    // DO NOT TRUST $_FILES['thumbnail']['mime'] VALUE !!
                    // Check MIME Type by yourself.
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    if (false === $ext = array_search(
                        $finfo->file($_FILES['thumbnail']['tmp_name']),
                        array(
                            'jpg' => 'image/jpeg',
                            'png' => 'image/png',
                            'gif' => 'image/gif',
                        ),
                        true
                    )) {
                        throw new RuntimeException('Format de fichier invalide');
                    }

                    // You should name it uniquely.
                    // DO NOT USE $_FILES['thumbnail']['name'] WITHOUT ANY VALIDATION !!
                    // On this example, obtain safe unique name from its binary data.
                    if (!move_uploaded_file(
                        $_FILES['thumbnail']['tmp_name'],
                        sprintf(
                            'uploads/%s.%s',
                            $name = sha1_file($_FILES['thumbnail']['tmp_name']),
                            $ext
                        )
                    )) {
                        throw new RuntimeException('Impossible d\'enregistrer le fichier');
                    }

                    $thumbnail_url = sprintf(
                        'uploads/%s.%s',
                        $name,
                        $ext
                    );
                } catch (RuntimeException $e) {

                    $viewData['errors']['file'] = $e->getMessage();
                }
            }

            if (!empty($_POST['title'])) {
                $title = trim($_POST['title']);
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            } else {
                $viewData['errors']['title'] = 'Besoin d\'un titre';
            }
            if (!empty($_POST['content'])) {
                $content = $_POST['content'];
            } else {
                $viewData['errors']['content'] = 'Besoin de contenu';
            }
            
            $link_online = $_POST['link_online'];
            $link_repo = $_POST['link_repo'];

            if (!empty($_POST['category'])) {
                if ($_POST['category'] != 'default') {
                    $category_id = $_POST['category'];
                } elseif (!empty($_POST['category_name'])) {
                    $prepare = $this->db->prepare(
                        'INSERT INTO project_categories (name)
                         VALUES (:name)'
                    );
                    $prepare->bindValue('name', $_POST['category_name']);
                    $prepare->execute();
                    $category_id = $this->db->lastInsertId();
                }
            }

            if (empty($viewData['errors'])) {
                $prepare = $this->db->prepare('
                    UPDATE
                        projects
                    SET
                        slug = :slug, title = :title, content = :content, category_id = :category_id, thumbnail_url = :thumbnail_url, link_online = :link_online, link_repo = :link_repo
                    WHERE
                        slug = :original_slug
                ');

                $prepare->bindValue(':slug', $slug);
                $prepare->bindValue(':title', $title);
                $prepare->bindValue(':content', $content);
                $prepare->bindValue(':category_id', $category_id);
                $prepare->bindValue(':thumbnail_url', $thumbnail_url);
                $prepare->bindValue(':link_online', $link_online);
                $prepare->bindValue(':link_repo', $link_repo);
                $prepare->bindValue(':original_slug', $arguments['slug']);

                if ($prepare->execute()) {
                    return $response->withRedirect($this->router->pathFor('admin'));
                }
            }

            return $this->view->render($response, 'pages/edit-project.twig', $viewData);
        }
    );