<?php

class CategoryManager
{
    private $bdd;
    private static $instance = null;

    private function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    public static function getInstance($bdd = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($bdd);
        }
        return self::$instance;
    }

    public function getAllCategories()
    {
        try {
            $req = $this->bdd->query("SELECT * FROM categories ORDER BY category_name ASC");
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getCategoryById($categoryId)
    {
        try {
            $req = $this->bdd->prepare("SELECT * FROM categories WHERE category_id = :category_id");
            $req->bindValue(":category_id", $categoryId, PDO::PARAM_INT);
            $req->execute();
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getCategoryByName($categoryName)
    {
        try {
            $req = $this->bdd->prepare("SELECT * FROM categories WHERE category_name = :category_name");
            $req->bindValue(":category_name", $categoryName);
            $req->execute();
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function createCategory($categoryName)
    {
        try {
            $req = $this->bdd->prepare("INSERT INTO categories (category_name) VALUES (:category_name)");
            $req->bindValue(":category_name", $categoryName);
            return $req->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateCategory($categoryId, $categoryName)
    {
        try {
            $req = $this->bdd->prepare("UPDATE categories SET category_name = :category_name WHERE category_id = :category_id");
            $req->bindValue(":category_name", $categoryName);
            $req->bindValue(":category_id", $categoryId, PDO::PARAM_INT);
            return $req->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteCategory($categoryId)
    {
        try {
            // Vérifier si la catégorie est utilisée
            $req = $this->bdd->prepare("SELECT COUNT(*) as count FROM articles WHERE category_id = :category_id");
            $req->bindValue(":category_id", $categoryId, PDO::PARAM_INT);
            $req->execute();
            $result = $req->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                return false; // La catégorie est utilisée
            }

            $req = $this->bdd->prepare("DELETE FROM categories WHERE category_id = :category_id");
            $req->bindValue(":category_id", $categoryId, PDO::PARAM_INT);
            return $req->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getCategoryStats()
    {
        try {
            $req = $this->bdd->query("
                SELECT 
                    c.category_id,
                    c.category_name,
                    COUNT(a.article_id) as article_count,
                    COUNT(CASE WHEN a.article_status = 'publie' THEN 1 END) as published_count
                FROM categories c
                LEFT JOIN articles a ON c.category_id = a.category_id
                GROUP BY c.category_id, c.category_name
                ORDER BY c.category_name ASC
            ");
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}