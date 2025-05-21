<?php

class ArticleManager
{
    private $bdd;

    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    public function createArticle($title, $content, $categoryId, $authorId, $tags, $image = null)
    {
        try {
            // Validation de l'email
            if (!$this->isEmailValidated($authorId)) {
                return [
                    'success' => false,
                    'message' => "Vous devez valider votre adresse email pour pouvoir créer un article."
                ];
            }

            // Traitement des tags
            $tagsJson = $this->formatTags($tags);

            // Traitement de l'image
            $imageName = $this->handleImage($image);

            $date = date("Y-m-d");

            $req = $this->bdd->prepare("
                INSERT INTO articles(
                    article_title, 
                    article_date, 
                    article_content, 
                    article_tags, 
                    article_status, 
                    article_img, 
                    category_id, 
                    author_id
                ) VALUES(
                    :article_title, 
                    :article_date, 
                    :article_content, 
                    :article_tags, 
                    :article_status, 
                    :article_img, 
                    :category_id, 
                    :author_id
                )
            ");

            $req->bindValue(":article_title", $title);
            $req->bindValue(":article_date", $date);
            $req->bindValue(":article_content", $content);
            $req->bindValue(":article_tags", $tagsJson);
            $req->bindValue(":article_status", "en_attente");
            $req->bindValue(":article_img", $imageName);
            $req->bindValue(":category_id", $categoryId);
            $req->bindValue(":author_id", $authorId);

            if ($req->execute()) {
                return [
                    'success' => true,
                    'message' => "Votre article a été soumis avec succès et est en attente de validation par un administrateur."
                ];
            }

            return [
                'success' => false,
                'message' => "Erreur lors de la création de l'article"
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => "Une erreur est survenue lors de la création de l'article"
            ];
        }
    }

    public function getArticlesByCategory($categoryId, $page = 1, $articlesPerPage = 3)
    {
        try {
            $offset = ($page - 1) * $articlesPerPage;

            $sql = "
                SELECT a.*, COALESCE(u.username, 'Utilisateur supprimé') as username, c.category_name 
                FROM articles a 
                LEFT JOIN users u ON a.author_id = u.user_id 
                JOIN categories c ON a.category_id = c.category_id 
                WHERE a.article_status = 'publie' 
            ";

            $params = [];

            if ($categoryId !== null) {
                $sql .= "AND a.category_id = :category_id ";
                $params[':category_id'] = $categoryId;
            }

            $sql .= "ORDER BY a.article_date DESC LIMIT :limit OFFSET :offset";

            $req = $this->bdd->prepare($sql);

            foreach ($params as $key => $value) {
                $req->bindValue($key, $value);
            }

            $req->bindValue(":limit", $articlesPerPage, PDO::PARAM_INT);
            $req->bindValue(":offset", $offset, PDO::PARAM_INT);
            $req->execute();

            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    private function isEmailValidated($userId)
    {
        $req = $this->bdd->prepare("SELECT user_email_validation FROM users WHERE user_id = :user_id");
        $req->bindValue(":user_id", $userId);
        $req->execute();
        $user = $req->fetch(PDO::FETCH_ASSOC);

        return $user && $user['user_email_validation'] == 1;
    }

    private function formatTags($tags)
    {
        if (is_string($tags)) {
            $tagsArray = explode(',', $tags);
        } else {
            $tagsArray = $tags;
        }

        $tagsArray = array_map(function ($tag) {
            $tag = str_replace('value:', '', $tag);
            return ['value' => trim($tag)];
        }, $tagsArray);

        $tagsArray = array_filter($tagsArray, function ($tag) {
            return !empty($tag['value']);
        });

        return json_encode($tagsArray);
    }

    private function handleImage($image)
    {
        if (empty($image["name"])) {
            return "default_avatar.png";
        }

        // Logique de traitement d'image à implémenter
        return "default_avatar.png";
    }
}