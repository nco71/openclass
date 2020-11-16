<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* core/themes/claro/templates/admin/tablesort-indicator.html.twig */
class __TwigTemplate_f9b8294137d7ba173d846f496e100bc3ba7f766b57a1ec47733cc5df27e6018b extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $tags = array("set" => 13, "if" => 19);
        $filters = array("escape" => 18, "t" => 22);
        $functions = array();

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['escape', 't'],
                []
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

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
        $macros = $this->macros;
        // line 13
        $context["classes"] = [0 => "tablesort", 1 => ("tablesort--" . $this->sandbox->ensureToStringAllowed(        // line 15
($context["style"] ?? null), 15, $this->source))];
        // line 18
        echo "<span";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [0 => ($context["classes"] ?? null)], "method", false, false, true, 18), 18, $this->source), "html", null, true);
        echo ">
  ";
        // line 19
        if (twig_in_filter(($context["style"] ?? null), [0 => "asc", 1 => "desc"])) {
            // line 20
            echo "    <span class=\"visually-hidden\">
      ";
            // line 21
            if ((($context["style"] ?? null) == "asc")) {
                // line 22
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Sort ascending"));
                echo "
      ";
            } else {
                // line 24
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Sort descending"));
                echo "
      ";
            }
            // line 26
            echo "    </span>
  ";
        }
        // line 28
        echo "</span>
";
    }

    public function getTemplateName()
    {
        return "core/themes/claro/templates/admin/tablesort-indicator.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  91 => 28,  87 => 26,  82 => 24,  77 => 22,  75 => 21,  72 => 20,  70 => 19,  65 => 18,  63 => 15,  62 => 13,);
    }

    public function getSourceContext()
    {
        return new Source("", "core/themes/claro/templates/admin/tablesort-indicator.html.twig", "/var/www/html/web/core/themes/claro/templates/admin/tablesort-indicator.html.twig");
    }
}
