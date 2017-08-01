<?php header('Content-Type: text/html; charset=utf-8');
session_start(); // Стартуем сессию
include_once("yii/framework/yii.php")?> 

    <!-- php authorisation -->
	
<?php $connection = mysqli_connect('localhost', 'root', '', 'aaa') or die(mysqli_error()); // Соединение с базой данных ?>

<?php if (isset($_POST['exit'])) // Отлавливаем нажатие на кнопку exit 
{
	
			unset($_SESSION['password']); // Чистим сессию пароля
			unset($_SESSION['login']); // Чистим сессию логина
			unset($_SESSION['id']); // Чистим сессию id
            unset($_SESSION['role']); // чистим сессию роли
}


class UserIdentity extends CUserIdentity
{
    private $_id;
    public function authenticate()
    {
        $record=User::model()->findByAttributes(array('username'=>$this->username));
        if($record===null)
            $this->errorCode=self::ERROR_USERNAME_INVALID;
        else if(!CPasswordHelper::verifyPassword($this->password,$record->password))
            $this->errorCode=self::ERROR_PASSWORD_INVALID;
        else
        {
            $this->_id=$record->id;
            $this->setState('title', $record->title);
            $this->errorCode=self::ERROR_NONE;
        }
        return !$this->errorCode;
    }
 
    public function getId()
    {
        return $this->_id;
    }
}

?>

<?php if (isset($_POST['submit'])) // Отлавливаем нажатие кнопки "Отправить"
{
	if (empty($_POST['login'])) // Если поле логин пустое
{
	echo '<script>alert("Поле логин не заполненно");</script>'; // То выводим сообщение об ошибке
}
elseif (empty($_POST['password'])) // Если поле пароль пустое
{
	echo '<script>alert("Поле пароль не заполненно");</script>'; // То выводим сообщение об ошибке
}
else  // Иначе если все поля заполненны
{    
		$login = $_POST['login']; // Записываем логин в переменную 
		$password = $_POST['password']; // Записываем пароль в переменную           
		$query = mysqli_query($connection, "SELECT UserID FROM `users` WHERE Login = '$login' AND Password = '$password'"); // Формируем переменную с запросом к базе данных с проверкой пользователя
		$result = mysqli_fetch_array($query); // Формируем переменную с исполнением запроса к БД 
if (empty($result['UserID'])) // Если запрос к бд не возвразяет id пользователя
{
	echo '<script>alert("Неверные Логин или Пароль");</script>'; // Значит такой пользователь не существует или не верен пароль
}
else // Если возвращяем id пользователя, выполняем вход под ним
	{
		$_SESSION['password'] = $password; // Заносим в сессию  пароль
		$_SESSION['login'] = $login; // Заносим в сессию  логин
		$_SESSION['id'] = $result['UserID']; // Заносим в сессию  id
		$query =mysqli_query($connection,"SELECT Role FROM users WHERE Login='$login' AND Password = '$password'");
		$role = mysqli_fetch_array($query);
		$_SESSION['role'] = $role['Role'];
		
		echo '<div align="center">Вы успешно вошли в систему: '.$_SESSION['login'].'</div>'; // Выводим сообщение что пользователь авторизирован        
	}     
}
if ($_SESSION['role'] === "operator")	
{
	header('Location: http://localhost/sait/uploading.php ');
}	
} 
 elseif ($_SESSION['role'] === "admin") {
 echo "<div id='Role' class='hidden'>1</div>";
}
elseif ($_SESSION['role'] === "Operator") {
 echo "<div id='Role' class='hidden'>2</div>";
}

?>


<!DOCTYPE html>
<html lang="ru">
<head>
     <script src="https://yastatic.net/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    
    <meta charset="UTF-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name = "author" content = "Grigory Zdanovich & Andrey Zaitsev"/>
    <meta name = "reply-to" content = "gic0@bk.ru" />
    <meta name = "site-created" content = "24.07.2017" />
    <meta name = "generator" content = "Bootstrap" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name = "description" content = "Краткое описание этой страницы." />
    <meta name = "keywords" content = "страница, описание" />
    <meta name = "robots" content = "index,follow" />
    <title>Project Name</title>
    <link rel="stylesheet/less" type="text/css" href="stylesheet/bootstrap.css">
    <link rel="stylesheet/less" type="text/css" href="stylesheet/style.css">
    <script src="https://cdn.jsdelivr.net/less/2.7.2/less.min.js"></script>
</head>
<body>
    <div class="page-wrap">
    <!--меню-->
    
   <div class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            </button>
          <a class="navbar-brand" href="index.php">Project name</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">
            <li class="menuu"><a  href="order.php">Заказать</a></li>
            <li class="menuu"><a  href="uploading.php">Выгрузка</a></li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <span class="glyphicon glyphicon-user"></span> <span class="caret"></span></a>
                    <ul id="login-dp" class="dropdown-menu">
                        <li>
                            <li>
                            <div class="row">
                                <div class="col-md-12">
								<?php if ($_SESSION['id'] != '')
								{	
									echo '<form class="form" role="form"  action="index.php" method="post" id="login-nav">
                                        <div class="form-group">
                                            <label class="sr-only" for="exampleInputEmail2">Login</label>
                                           <div name="login" class="well well-sm">';
                                               echo $_SESSION['login'];
                                           echo'</div>
                                        </div>
                                        <div class="form-group">
                                            <label class="sr-only" for="exampleInputPassword2">Password</label>
                                           <div name="role" class="well well-sm">';
                                               echo $_SESSION['role'];
                                         echo'  </div>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" name="exit" class="btn btn-primary btn-block">Выйти</button>
                                        </div>
								</form>';
								}
									else
									{
										echo '<form class="form" role="form" action="index.php" method="post" id="login-nav">
                                        <div class="form-group">
                                            <label class="sr-only" for="exampleInputEmail2">Email address</label>
                                            <input type="text" class="form-control" id="exampleInputEmail2" placeholder="Email address" name="login" >
                                        </div>
                                        <div class="form-group">
                                            <label class="sr-only" for="exampleInputPassword2">Password</label>
                                            <input type="password" class="form-control" id="exampleInputPassword2" placeholder="Password" name ="password">
                                            <div class="help-block text-right"><a href="forget.php">Забыли пароль?</a></div>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary btn-block" name="submit" >Войти</button>
                                        </div>
                                        <div class="bottom text-center">
                                          <a href="reg.php">
                                          <b>Регистрация</b>
                                          </a>
                                        </div>
                                    </form>';
									}
									?>
                                </div>
                            </div>
                        </li>
          </ul>
              </li>
              </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>
    
    <!--реклама-->
    <div class="row">
      <div class="col-lg-12">
        <div class="container">
            <a id="advertising" href="order.php">
            <img id="advertising" class="img img-responsive center-block" src="https://zawebis.com/images/rklm/makovkin/makovkin_top.jpg">
            </a>
            <div class="AdmPic  hidden">
            ссылка:<input class="hrefPic" value="order.php">
            источник:<input class="srcPic" value="https://zawebis.com/images/rklm/makovkin/makovkin_top.jpg">
              <button id="AdmBtn" class="btn btn-default btn-xs pull-right"> Применить</button>
            </div>
        </div>
     </div>
    </div>
    
    <!--список городов-->
    <div class="row">
    <div class="col-lg-6 col-lg-offset-3">
    <div class="panel panel-default">
  <!-- Default panel contents -->
        <div class="panel-heading text-center">Список городов</div>
  <!-- Table -->
  
    <table class="table">
        <tbody>
          <tr>
            <td>Питер</td>
            <td>Ростов</td>
            <td>Вологда</td>
          </tr>
          <tr>
            <td>Новосибирск</td>
            <td>Курск</td>
            <td>Калиниград</td>
          </tr>
          <tr>
            <td>Ростов-на-дону</td>
            <td>Уфа</td>
            <td>Анапа</td>
          </tr>
          <tr>
            <td>Астрахань</td>
            <td>Днепр</td>
            <td>Москва</td>
          </tr>
        </tbody>
      </table>
</div>
   </div>
   </div>
    
    <!--список стран-->
    <div class="row">
        <div class="col-lg-8 col-lg-offset-2">
            <div class="container">
                <p class="list">Список стран</p>
            </div>
        </div>
    </div> 
    
    <!--список партнеров-->
    <div class="row">
    <div class="col-lg-6 col-lg-offset-3">
    <div class="panel panel-default">
  <!-- Default panel contents -->
        <div class="panel-heading">Наши партнеры</div>
  <!-- Table -->
  
    <table class="table">
        <tbody>
          <tr>
            <td>1</td>
            <td>Mark</td>
            <td>Otto</td>
            <td>@mdo</td>
          </tr>
          <tr>
            <td>2</td>
            <td>Jacob</td>
            <td>Thornton</td>
            <td>@fat</td>
          </tr>
          <tr>
            <td>3</td>
            <td>Larry</td>
            <td>the Bird</td>
            <td>@twitter</td>
          </tr>
        </tbody>
      </table>
</div>
   </div>
   </div>
    
    <!--шестеренки-->
    <div class="row">
      <div class="col-lg-12">
        <div class="container">
            <img class="img img-responsive center-block" src="jpg/gears.gif">
        </div>
     </div>
    </div>    
    
    <!--контакты-->
    <div class="row">
        <div class="col-lg-6 col-lg-offset-3">
               <div class="tel">
                      <div class="iframe">
                       <iframe class="map pull-right" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d35936.872307538666!2d37.58866284039507!3d55.740181701470085!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x46b54a459f0602bb%3A0x8622e396e504ed4c!2z0KHQvNC10YDRgtGMINCc0YPQttGM0Y_QvCwg0LrQvtC80LjRgdGB0LjQvtC90L3Ri9C5INC80LDQs9Cw0LfQuNC9!5e0!3m2!1sru!2sru!4v1499078184010" frameborder="0" style="border:0" allowfullscreen></iframe> 
                      </div>
              <p class="tel_c"> 
                   контакты:<br> 
                   Москва ул. Пж. д8<br> 
                   8-800-555-35-35<br> 
                   Факс 
              </p>    
               </div>
        </div>
    </div>
    
    <!--фуутер-->
   
</div>
 <footer class="site-footer">
   <table width="100%">
    <td width="33%">
     <p class="foot-p">ООО "Беспроводные Технологии"</p>
    </td>
    <td width="33%">
     <p class=" text-center foot-p">2017</p>
    </td>
    <td width="33%">
     <p class="pull-right foot-p">8-800-555-35-35</p>
    </td>
 </table>
</footer>
    
    
   
   
    <script src="js/hover_menu.js" type="text/javascript"></script>
    <script src="js/AdminPart.js" type="text/javascript"></script>
   
    
</body>
</html>