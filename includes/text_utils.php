<?php
/**
 * Nettoie le HTML de manière sécurisée
 * @param string $html Le contenu HTML à nettoyer
 * @return string Le HTML nettoyé
 */
function cleanHtml($html)
{
    // Liste des balises autorisées
    $allowedTags = '<p><h1><h2><h3><h4><h5><h6><ul><ol><li><strong><em><br><span><div><font>';

    // Nettoyer le HTML en gardant uniquement les balises autorisées
    return strip_tags($html, $allowedTags);
}

/**
 * Nettoie et tronque le HTML de manière sécurisée
 * @param string $html Le contenu HTML à nettoyer
 * @param int $length La longueur maximale souhaitée
 * @return string Le HTML nettoyé et tronqué
 */
function cleanAndTruncateHtml($html, $length = 500)
{
    // Liste des balises autorisées
    $allowedTags = '<p><h1><h2><h3><h4><h5><h6><ul><ol><li><strong><em><br><span><div><font>';

    // Nettoyer le HTML en gardant uniquement les balises autorisées
    $cleanHtml = strip_tags($html, $allowedTags);

    // Convertir les balises <br> en retours à la ligne
    $cleanHtml = str_replace(['<br>', '<br/>', '<br />'], "\n", $cleanHtml);

    // Supprimer les espaces et retours à la ligne au début et à la fin
    $cleanHtml = trim($cleanHtml);

    // Normaliser les espaces et retours à la ligne
    $cleanHtml = preg_replace('/\s+/', ' ', $cleanHtml);
    $cleanHtml = str_replace(' ', ' ', $cleanHtml);
    $cleanHtml = str_replace("\n", "\n", $cleanHtml);

    // Obtenir le texte brut sans HTML
    $plainText = strip_tags($cleanHtml);

    // Si le texte est plus court que la longueur souhaitée, retourner le HTML complet
    if (strlen($plainText) <= $length) {
        // Restaurer les balises <br> pour le HTML
        $cleanHtml = str_replace("\n", "<br>", $cleanHtml);
        return $cleanHtml;
    }

    // Tronquer le texte brut
    $truncatedText = substr($plainText, 0, $length);

    // Restaurer les balises <br> pour le HTML
    $truncatedText = str_replace("\n", "<br>", $truncatedText);

    // Retourner le texte tronqué avec les points de suspension
    return $truncatedText . '...';
}