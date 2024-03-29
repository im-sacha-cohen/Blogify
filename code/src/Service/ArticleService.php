<?php

namespace App\Service;

use App\Model\Class\Comment;
use App\Model\Class\Article;
use App\Model\ClassManager\ArticleManager;
use App\Model\ClassManager\CommentManager;
use Exception;

class ArticleService {
    private ArticleManager $articleManager;
    private ImportFileService $importFileService;
    private AuthService $authService;
    private CommentManager $commentManager;
    private $userId;

    public function __construct()
    {
        $this->articleManager = new ArticleManager();
        $this->importFileService = new ImportFileService();
        $this->authService = new AuthService();
        $this->commentManager = new CommentManager();
        $this->userId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
    }

    public function create(array $data) {
        $cover = $_FILES['cover'];

        if ($cover) {
            $data['cover'] = $cover['name'];
        }
       
        $mandatoryFields = ['title', 'teaser', 'content', 'coverCredit', 'cover'];
        $this->verifyMandatoryFields($data, $mandatoryFields);
        $data['cover'] =  $this->importFileService->verifyAndUploadFile($cover);

        $data['authorId'] = $this->userId;
        $article = new Article($data);
        // Keep line breaks after htmlspecialchars
        $article->setContent(
            nl2br($article->getContent())
        );
        $this->articleManager->create($article);
    }

    public function verifyMandatoryFields(array $data, array $mandatoryFields) {
        $errors = [];
        foreach($mandatoryFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[] = "$field est manquant ou vide";   
            }
        }
        
        if (count($errors) > 0) {
            $error = implode(', ', $errors);
            throw new Exception($error);
        }
    }

    public function addComment(array $data) {
        if ($this->authService->isLogged()) {
            $mandatoryFields = ['articleId', 'comment'];
            $this->verifyMandatoryFields($data, $mandatoryFields);
    
            $data['user_id'] = $this->userId;
            $comment = new Comment($data);
            $this->commentManager->create($comment);
        } else {
            throw new Exception('Vous devez être connecté', 401);
        }
    }

    public function delete(int $id) {
        if (isset($id) && !empty($id) && is_numeric($id)) {
            $article = $this->articleManager->findOneBy($id);

            if ($article !== null) {
                unlink('./assets/img/blog/' . $article->getCover());
                $this->articleManager->delete($id);
            } else {
                throw new Exception("Une erreur s'est produite en lien avec l'article");
            }
        } else {
            throw new Exception("Une erreur s'est produite en lien avec l'article");
        }
    }

    public function update(int $id, array $data) {
        if ($this->authService->isLogged() && $this->authService->isAdmin()) {
            if (isset($id) && !empty($id)) {
                $article = $this->articleManager->findOneBy($id);

                if ($article !== null) {
                    $mandatoryFields = ['title', 'teaser', 'content', 'coverCredit'];
                    $this->verifyMandatoryFields($data, $mandatoryFields);
                    $cover = isset($_FILES['cover']) ? filter_input(INPUT_POST, 'cover') : null;
 
                    if ($cover) {
                        $data['cover'] = $cover['name'];
                        $data['cover'] = $this->importFileService->verifyAndUploadFile($cover);
                    } else {
                        $data['cover'] = $article->getCover();
                    }
                    
                    $data['id'] = $article->getId();
                    $article = new Article($data);
                    // Keep line breaks after htmlspecialchars
                    $article->setContent(
                        nl2br($article->getContent())
                    );
                    $this->articleManager->update($article);

                    return array();
                }
            }

            throw new Exception("Une erreur s'est produite en lien avec l'ID.");
        }

        throw new Exception('Vous n\'êtes pas connecté(e) ou vous ne possédez pas les droits nécéssaires.', 401);
    }

    /**
     * @return array
     */
    public function findLastArticles(): array {
        $articlesFetched = $this->articleManager->findLastArticles();
        $articles = [];

        if (count($articlesFetched) > 0) {
            foreach($articlesFetched as $article) {
                $article['created_at'] = date_create($article['created_at']);
                $article['updated_at'] = $article['updated_at'] !== null ? date_create($article['updated_at']) : null;
                $article['authorId'] = (int) $article['author_id'];
                
                $articleObject = new Article($article);
                $articles[] = $articleObject->jsonSerialize();
            }
        }

        return $articles;
    }

    /**
     * This function is not used yet.
     * This function should prevent HTML code duplication in index & blog view
     * How to inject the service into HTML file ?
     * 
     * @param array $article
     * 
     * @return string
     */
    public function getArticleTemplate(array $article): string {
        return '
            <div class="card">
                <a href="article?id=<?= $article[' . $article['id'] . '] ?>"></a>
                <div class="cover" style="background-image: url(../../assets/img/blog/'. $article['cover'] . ')"></div>
                <div class="bottom">
                    <h3>' . $article['title'] . '</h3>
                    <span>Le 
                        ' . strftime('%d %B', strtotime($article['createdAt']['date']))
                        . ' ⸱ Sacha COHEN
                    </span>
                </div>
            </div>
        ';
    }

    /**
     * @return array
     */
    public function findAll(): array {
        $articles = $this->articleManager->findAll();
        
        $articlesSerialized = [];
        
        foreach($articles as $article) {
            $articlesSerialized[] = $article->jsonSerialize();
        }

        return $articlesSerialized;
    }
}