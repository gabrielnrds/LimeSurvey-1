<?php
class ProjectController extends LSYii_Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
   * @var array variável necessária para o breadcrumbs da view
   */
	public $breadcrumbs = array();

	/**
   * @var array variável necessária para o menu de ações (se houver na view)
   */
  public $menu = array();

	public $aData = array(); // Variável de dados genérica usada em views admin

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update', 'sendSurvey'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


	/**
	 * NOVA ACTION: Envia o e-mail usando LimeMailer (SMTP do Sistema)
	 */
	public function actionSendSurvey($id)
	{
			// 1. Carrega o projeto
			$project = $this->loadModel($id);

			// 2. ID da pesquisa (Ajuste conforme necessário)
			$surveyId = 999216; 

			// 3. Gera o link absoluto
			$surveyLink = Yii::app()->createAbsoluteUrl("survey/index", array(
				"sid" => $surveyId, 
				"newtest" => "Y",
				"pid" => $project->id //
			));

			// 4. Monta o corpo do e-mail em HTML
			// Usamos CHtml::encode para evitar injeção de código malicioso via nome do usuário
			$body  = "<p>Olá <strong>" . CHtml::encode($project->coordinator) . "</strong>,</p>";
			$body .= "<p>Por favor, responda o formulário de avaliação sobre o projeto recém-cadastrado: <em>" . CHtml::encode($project->title) . "</em>.</p>";
			$body .= "<p>Clique no botão abaixo para acessar:</p>";
			$body .= "<p><a href='" . $surveyLink . "' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Acessar Formulário</a></p>";
			$body .= "<p><small>Ou acesse pelo link: <a href='" . $surveyLink . "'>" . $surveyLink . "</a></small></p>";
			$body .= "<p>Atenciosamente,<br>Equipe de Projetos.</p>";

			// 5. Instancia o LimeMailer
			// Ele carrega automaticamente as configurações do config.php ou do banco de dados
			try {
					$mailer = new LimeMailer();
					
					// Para quem vai o e-mail
					$mailer->AddAddress($project->coordinator_email);
					
					// Assunto
					$mailer->Subject = "Avaliação de Projeto: " . $project->title;
					
					// Define que é HTML
					$mailer->msgHTML($body);
					
					// Opcional: Se quiser forçar um remetente diferente do padrão do sistema
					// $mailer->setFrom('admin@seusite.com', 'Sistema de Projetos');

					// Tenta enviar
					if ($mailer->send()) {
							Yii::app()->user->setFlash('success', "Convite enviado via SMTP para " . $project->coordinator_email);
							
							// Atualiza status para 'Em Andamento'
							if ($project->status == 'Cadastrado') {
									$project->status = 'Em Andamento';
									$project->save();
							}
					} else {
							// Captura erro específico do PHPMailer/LimeMailer
							Yii::app()->user->setFlash('error', "Erro no envio: " . $mailer->ErrorInfo);
					}

			} catch (Exception $e) {
					// Captura erros de exceção na instância do Mailer
					Yii::app()->user->setFlash('error', "Erro crítico ao instanciar LimeMailer: " . $e->getMessage());
			}

			// Redireciona de volta para a lista
			$this->redirect(array('index'));
	}

	/**
	 * ACTION DE RETORNO: Chamada automaticamente ao fim da pesquisa
	 */
	public function actionProcessarRetorno()
	{
    // Recebemos o ID da pesquisa (sid) e o ID da resposta (rid) via GET
    $surveyId = Yii::app()->request->getQuery('sid');
    $responseId = Yii::app()->request->getQuery('rid');

    if (!$surveyId || !$responseId) {
        throw new CHttpException(400, 'Parâmetros inválidos.');
    }

    // 1. Buscar a resposta na tabela dinâmica do LimeSurvey
    // As tabelas de resposta têm o nome: lime_survey_{sid}
    $tableResponse = "{{survey_" . $surveyId . "}}";
    
    // Precisamos descobrir os nomes exatos das colunas.
    // O LimeSurvey nomeia colunas como: {sid}X{groupId}X{questionId}
    // Para simplificar, vamos usar o Alias (Código) que definimos no Passo 1 se o survey estiver ativado com mapeamento
    // OU, o jeito mais robusto via SQL direto buscando pelo token/id.

    // CONSULTA SQL PARA PEGAR OS DADOS BRUTOS
    // Nota: Assume que você sabe os códigos das perguntas (project_id, status_survey, hours_survey)
    // Se não usar SurveyLogic, precisa mapear na mão. Abaixo um exemplo genérico:
    
    $response = Yii::app()->db->createCommand("SELECT * FROM {$tableResponse} WHERE id = :rid")
        ->bindValue(':rid', $responseId)
        ->queryRow();

    if ($response) {
			// AQUI VOCÊ PRECISA IDENTIFICAR AS COLUNAS CERTAS.
			// O jeito mais fácil é fazer um "dump" na primeira vez para ver os nomes das colunas, 
			// ou usar o getColumnName do LimeSurvey, mas vamos tentar buscar pelo mapeamento do Project ID.
			
			// Vamos supor que você inspecionou a tabela e viu que:
			// project_id está na coluna: 123456X10X1 (exemplo)
			// Mas como isso muda, vamos usar a lógica de negócio:
			
			// Recupere o ID do Projeto que salvamos na pergunta oculta
			// OBS: Você terá que verificar no seu banco qual o nome da coluna correta para a pergunta 'project_id'
			// Uma alternativa rápida: Enviar o ID do projeto TAMBÉM na URL de retorno (ver passo 4).
			
			// Vamos simplificar e pegar o ID do projeto da URL (ver passo 4), é mais seguro.
			$projectId = Yii::app()->request->getQuery('projectId'); 
			
			// Pega os valores da resposta (Ajuste os nomes das colunas conforme seu banco real do LS)
			// Dica: No LimeSurvey, ative "Exportar códigos de resposta em vez de texto completo" se preferir.
			
			// Exemplo: Colunas fictícias, substitua pelas reais da tabela lime_survey_123456
			// $statusRes = $response['123456X1X1']; 
			// $horasRes  = $response['123456X1X2'];
			
			// MODO PREGUIÇOSO (Mas funcional se as perguntas forem as últimas):
			// Array values pega os valores sem se importar com as chaves
			$values = array_values($response); 
			// Você teria que saber a posição, o que é arriscado.
			
			// MODO CORRETO (Usando API interna do LS para pegar resposta pelo código):
			// Isso requer que o helper esteja carregado
			$oSurvey = Survey::model()->findByPk($surveyId);
			// ... Lógica complexa de API ...

			// MODO PRÁTICO PARA SEU CENÁRIO:
			// Vamos confiar que você passará os dados importantes via URL de retorno (Passo 4).
			// Isso evita ter que ler a tabela dinâmica do banco.
  	}
	}
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		
		$model=new Project;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Project']))
		{
			$model->attributes=$_POST['Project'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Project']))
		{
			$model->attributes=$_POST['Project'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$model = new Project;

		if(isset($_POST['Project']))
		{
				$model->attributes = $_POST['Project'];
				$model->horas_atuais = 0;
				$model->status = 'Cadastrado';
				
				// Validar e Salvar
				if($model->save()) {
						Yii::app()->user->setFlash('success', "Projeto salvo com sucesso!");
						$this->refresh();
				}
		}

		$this->render('index',array(
			'model' => $model,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Project('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Project']))
			$model->attributes=$_GET['Project'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Project the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Project::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Project $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='project-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
