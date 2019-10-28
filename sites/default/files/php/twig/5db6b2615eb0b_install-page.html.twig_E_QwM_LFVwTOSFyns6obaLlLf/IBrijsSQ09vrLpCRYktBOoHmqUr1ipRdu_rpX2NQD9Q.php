<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* core/themes/seven/templates/install-page.html.twig */
class __TwigTemplate_47cb2105b41e353bfed3ec58e84f498a5cfbde2cb99100c6353150e62e7af7ff extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $tags = ["if" => 15];
        $filters = ["escape" => 17];
        $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['if'],
                ['escape'],
                []
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->getSourceContext());

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 12
        echo "<div class=\"layout-container\">

  <header role=\"banner\">
    ";
        // line 15
        if (($context["site_name"] ?? null)) {
            // line 16
            echo "      <h1 class=\"page-title\">
        ";
            // line 17
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["site_name"] ?? null)), "html", null, true);
            echo "
        ";
            // line 18
            if (($context["site_version"] ?? null)) {
                // line 19
                echo "          <span class=\"site-version\">";
                echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["site_version"] ?? null)), "html", null, true);
                echo "</span>
        ";
            }
            // line 21
            echo "      </h1>
    ";
        }
        // line 23
        echo "  </header>

  ";
        // line 25
        if ($this->getAttribute(($context["page"] ?? null), "sidebar_first", [])) {
            // line 26
            echo "    <aside class=\"layout-sidebar-first\" role=\"complementary\">
      ";
            // line 27
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($this->getAttribute(($context["page"] ?? null), "sidebar_first", [])), "html", null, true);
            echo "
    </aside>";
            // line 29
            echo "  ";
        }
        // line 30
        echo "
  <main role=\"main\">
    ";
        // line 32
        if (($context["title"] ?? null)) {
            // line 33
            echo "      <h2 class=\"heading-a\">";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["title"] ?? null)), "html", null, true);
            echo "</h2>
    ";
        }
        // line 35
        echo "    ";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($this->getAttribute(($context["page"] ?? null), "highlighted", [])), "html", null, true);
        echo "
    ";
        // line 36
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($this->getAttribute(($context["page"] ?? null), "content", [])), "html", null, true);
        echo "
  </main>

  ";
        // line 39
        if ($this->getAttribute(($context["page"] ?? null), "sidebar_second", [])) {
            // line 40
            echo "    <aside class=\"layout-sidebar-second\" role=\"complementary\">
      ";
            // line 41
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($this->getAttribute(($context["page"] ?? null), "sidebar_second", [])), "html", null, true);
            echo "
    </aside>";
            // line 43
            echo "  ";
        }
        // line 44
        echo "
  ";
        // line 45
        if ($this->getAttribute(($context["page"] ?? null), "page_bottom", [])) {
            // line 46
            echo "    <footer role=\"contentinfo\">
      ";
            // line 47
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($this->getAttribute(($context["page"] ?? null), "page_bottom", [])), "html", null, true);
            echo "
    </footer>
  ";
        }
        // line 50
        echo "
</div>";
    }

    public function getTemplateName()
    {
        return "core/themes/seven/templates/install-page.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  146 => 50,  140 => 47,  137 => 46,  135 => 45,  132 => 44,  129 => 43,  125 => 41,  122 => 40,  120 => 39,  114 => 36,  109 => 35,  103 => 33,  101 => 32,  97 => 30,  94 => 29,  90 => 27,  87 => 26,  85 => 25,  81 => 23,  77 => 21,  71 => 19,  69 => 18,  65 => 17,  62 => 16,  60 => 15,  55 => 12,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "core/themes/seven/templates/install-page.html.twig", "/var/www/html/core/themes/seven/templates/install-page.html.twig");
    }
}
