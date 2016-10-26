<?php

namespace Earlysettler\Megamenu\Block\Html;

class Topmenu extends \Magento\Theme\Block\Html\Topmenu
{
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('Magento_Theme::html/topmenu.phtml');
        }
        return $this->fetchView($this->getTemplateFile());
    }

    /**
     * Add sub menu HTML code for current menu item
     *
     * @param \Magento\Framework\Data\Tree\Node $child
     * @param string $childLevel
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string HTML code
     */
    protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        $html = '';
        $showCmsBlock = false;
        $megamenu_module_active = true;
        if(!$megamenu_module_active) {
            return parent::_addSubMenu($child, $childLevel, $childrenWrapClass, $limit);
        }
        if($childLevel == 0) {
            $featuredBlock =  $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId(str_replace(' ','-',strtolower($child->getName())).'-menu-featured')->toHtml();
            $featuredBlock = ($featuredBlock)?'<div class="menu-featured">'.$featuredBlock.'</div>':'';
            $promosBlock =  $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId(str_replace(' ','-',strtolower($child->getName())).'-menu-footer')->toHtml();
            $promosBlock = ($promosBlock)?'<div class="menu-footer">'.$promosBlock.'</div>':'';
            $showCmsBlock = false;
            $htmlCmsBlock = '';
            if($featuredBlock || $promosBlock) {
                $htmlCmsBlock .= $featuredBlock;
                $htmlCmsBlock .= $promosBlock;
                $showCmsBlock = true;
            }

            if ($child->hasChildren() OR $showCmsBlock) {
                $MegaMenuWrapClass = 'nav-mega-container';
                //$html .= '<div class="'. $MegaMenuWrapClass .(($showCmsBlock)?' has-cms-block':'').'">';
            }
            if (!$child->hasChildren()) {
                $html .= '<ul class="level' . $childLevel . ' submenu">';
                if($showCmsBlock) {
                    $html .=    '<li class="level'. ($childLevel+1) .' parent nav-block-container">';
                    $html .=    $htmlCmsBlock;
                    $html .=    '</li>';
                    //$html .= '</div>';
                }
                $html .= '</ul>';
            }
        }

        if (!$child->hasChildren()) {
            return $html;
        }

        $colStops = null;
        if ($childLevel == 0 && $limit) {
            $colStops = $this->_columnBrake($child->getChildren(), $limit);
        }

        $html .= '<ul class="level' . $childLevel . ' submenu">';
        $html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);
        if($showCmsBlock) {
            $html .=    '<li class="level'. ($childLevel+1) .' parent nav-block-container">';
            $html .=    $htmlCmsBlock;
            $html .=    '</li>';
        }
        $html .= '</ul>';

        if($childLevel == 0) {
            //$html .= '</div>';
        }

        return $html;
    }

    /**
     * Returns array of menu item's classes
     *
     * @param \Magento\Framework\Data\Tree\Node $item
     * @return array
     */
    protected function _getMenuItemClasses(\Magento\Framework\Data\Tree\Node $item)
    {
        $classes = [];
        if($item->getLevel()== 0) {
            $classes[] = 'nav-mega-container';
        }


        $classes[] = 'level' . $item->getLevel();
        $classes[] = $item->getPositionClass();

        if ($item->getIsFirst()) {
            $classes[] = 'first';
        }

        if ($item->getIsActive()) {
            $classes[] = 'active';
        } elseif ($item->getHasActive()) {
            $classes[] = 'has-active';
        }

        if ($item->getIsLast()) {
            $classes[] = 'last';
        }

        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }

        if ($item->hasChildren()) {
            $classes[] = 'parent';
        }

        return $classes;
    }
}