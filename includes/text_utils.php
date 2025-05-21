<?php
/**
 * Nettoie le HTML de manière sécurisée
 * @param string $html Le contenu HTML à nettoyer
 * @return string Le HTML nettoyé
 */
function cleanHtml($html)
{
    // Liste des balises autorisées
    $allowedTags = '<p><h1><h2><h3><h4><h5><h6><ul><ol><li><strong><em><br><span>';

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
    $allowedTags = '<p><h1><h2><h3><h4><h5><h6><ul><ol><li><strong><em><br><span>';

    // Nettoyer le HTML en gardant uniquement les balises autorisées
    $cleanHtml = strip_tags($html, $allowedTags);

    // Obtenir le texte brut sans HTML
    $plainText = strip_tags($cleanHtml);

    // Si le texte est plus court que la longueur souhaitée, retourner le HTML complet
    if (strlen($plainText) <= $length) {
        return $cleanHtml;
    }

    // Tronquer le texte brut
    $truncatedText = substr($plainText, 0, $length);

    // Retourner le texte tronqué avec les points de suspension
    return $truncatedText . '...';
}