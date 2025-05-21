<?php

class Pagination
{
    private $totalItems;
    private $itemsPerPage;
    private $currentPage;
    private $totalPages;
    private $baseUrl;

    public function __construct($totalItems, $itemsPerPage, $currentPage, $baseUrl = '')
    {
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = max(1, min($currentPage, ceil($totalItems / $itemsPerPage)));
        $this->totalPages = ceil($totalItems / $itemsPerPage);
        $this->baseUrl = $baseUrl;
    }

    public function getOffset()
    {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }

    public function getLimit()
    {
        return $this->itemsPerPage;
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function getTotalPages()
    {
        return $this->totalPages;
    }

    public function hasNextPage()
    {
        return $this->currentPage < $this->totalPages;
    }

    public function hasPreviousPage()
    {
        return $this->currentPage > 1;
    }

    public function getNextPageUrl()
    {
        if ($this->hasNextPage()) {
            return $this->buildPageUrl($this->currentPage + 1);
        }
        return null;
    }

    public function getPreviousPageUrl()
    {
        if ($this->hasPreviousPage()) {
            return $this->buildPageUrl($this->currentPage - 1);
        }
        return null;
    }

    public function getPageUrl($page)
    {
        return $this->buildPageUrl($page);
    }

    private function buildPageUrl($page)
    {
        $url = $this->baseUrl;
        $separator = strpos($url, '?') !== false ? '&' : '?';
        return $url . $separator . 'page=' . $page;
    }

    public function render()
    {
        if ($this->totalPages <= 1) {
            return '';
        }

        $html = '<nav aria-label="Navigation des articles" class="my-4">';
        $html .= '<ul class="pagination justify-content-center">';

        // Bouton précédent
        if ($this->hasPreviousPage()) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->getPreviousPageUrl() . '" aria-label="Précédent">';
            $html .= '<span aria-hidden="true">&larr;</span> Précédent</a></li>';
        }

        // Numéros de page
        for ($i = 1; $i <= $this->totalPages; $i++) {
            $activeClass = ($i == $this->currentPage) ? 'active' : '';
            $html .= '<li class="page-item ' . $activeClass . '">';
            $html .= '<a class="page-link" href="' . $this->getPageUrl($i) . '">' . $i . '</a></li>';
        }

        // Bouton suivant
        if ($this->hasNextPage()) {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $this->getNextPageUrl() . '" aria-label="Suivant">';
            $html .= 'Suivant <span aria-hidden="true">&rarr;</span></a></li>';
        }

        $html .= '</ul></nav>';

        return $html;
    }
}