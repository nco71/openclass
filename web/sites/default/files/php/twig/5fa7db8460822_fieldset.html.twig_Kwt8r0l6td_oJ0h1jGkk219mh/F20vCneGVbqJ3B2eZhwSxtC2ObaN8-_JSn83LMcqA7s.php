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

/* core/themes/claro/templates/fieldset.html.twig */
class __TwigTemplate_eb431c6701c4bf9a48210f483dead67619589a6c942e07cf35d2171c42769daf extends \Twig\Template
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
        $tags = array("set" => 11, "if" => 50);
        $filters = array("escape" => 48);
        $functions = array();

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['escape'],
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
        // line 11
        $context["classes"] = [0 => "fieldset", 1 => ((twig_get_attribute($this->env, $this->source,         // line 13
($context["attributes"] ?? null), "hasClass", [0 => "fieldgroup"], "method", false, false, true, 13)) ? ("fieldset--group") : ("")), 2 => "js-form-item", 3 => "form-item", 4 => "js-form-wrapper", 5 => "form-wrapper"];
        // line 21
        $context["wrapper_classes"] = [0 => "fieldset__wrapper", 1 => ((twig_get_attribute($this->env, $this->source,         // line 23
($context["attributes"] ?? null), "hasClass", [0 => "fieldgroup"], "method", false, false, true, 23)) ? ("fieldset__wrapper--group") : (""))];
        // line 27
        $context["legend_span_classes"] = [0 => "fieldset__label", 1 => ((twig_get_attribute($this->env, $this->source,         // line 29
($context["attributes"] ?? null), "hasClass", [0 => "fieldgroup"], "method", false, false, true, 29)) ? ("fieldset__label--group") : ("")), 2 => ((        // line 30
($context["required"] ?? null)) ? ("js-form-required") : ("")), 3 => ((        // line 31
($context["required"] ?? null)) ? ("form-required") : (""))];
        // line 35
        $context["legend_classes"] = [0 => "fieldset__legend", 1 => (((twig_get_attribute($this->env, $this->source,         // line 37
($context["attributes"] ?? null), "hasClass", [0 => "fieldgroup"], "method", false, false, true, 37) &&  !twig_get_attribute($this->env, $this->source, ($context["attributes"] ?? null), "hasClass", [0 => "form-composite"], "method", false, false, true, 37))) ? ("fieldset__legend--group") : ("")), 2 => ((twig_get_attribute($this->env, $this->source,         // line 38
($context["attributes"] ?? null), "hasClass", [0 => "form-composite"], "method", false, false, true, 38)) ? ("fieldset__legend--composite") : ("")), 3 => (((        // line 39
($context["title_display"] ?? null) == "invisible")) ? ("fieldset__legend--invisible") : ("fieldset__legend--visible"))];
        // line 43
        $context["description_classes"] = [0 => "fieldset__description"];
        // line 47
        echo "
<fieldset";
        // line 48
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [0 => ($context["classes"] ?? null)], "method", false, false, true, 48), 48, $this->source), "html", null, true);
        echo ">
  ";
        // line 50
        echo "  ";
        if (twig_get_attribute($this->env, $this->source, ($context["legend"] ?? null), "title", [], "any", false, false, true, 50)) {
            // line 51
            echo "  <legend";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["legend"] ?? null), "attributes", [], "any", false, false, true, 51), "addClass", [0 => ($context["legend_classes"] ?? null)], "method", false, false, true, 51), 51, $this->source), "html", null, true);
            echo ">
    <span";
            // line 52
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["legend_span"] ?? null), "attributes", [], "any", false, false, true, 52), "addClass", [0 => ($context["legend_span_classes"] ?? null)], "method", false, false, true, 52), 52, $this->source), "html", null, true);
            echo ">";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["legend"] ?? null), "title", [], "any", false, false, true, 52), 52, $this->source), "html", null, true);
            echo "</span>
  </legend>
  ";
        }
        // line 55
        echo "
  <div";
        // line 56
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["content_attributes"] ?? null), "addClass", [0 => ($context["wrapper_classes"] ?? null)], "method", false, false, true, 56), 56, $this->source), "html", null, true);
        echo ">
    ";
        // line 57
        if (($context["inline_items"] ?? null)) {
            // line 58
            echo "      <div class=\"container-inline\">
    ";
        }
        // line 60
        echo "
    ";
        // line 61
        if (($context["prefix"] ?? null)) {
            // line 62
            echo "      <span class=\"fieldset__prefix\">";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["prefix"] ?? null), 62, $this->source), "html", null, true);
            echo "</span>
    ";
        }
        // line 64
        echo "    ";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["children"] ?? null), 64, $this->source), "html", null, true);
        echo "
    ";
        // line 65
        if (($context["suffix"] ?? null)) {
            // line 66
            echo "      <span class=\"fieldset__suffix\">";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["suffix"] ?? null), 66, $this->source), "html", null, true);
            echo "</span>
    ";
        }
        // line 68
        echo "    ";
        if (($context["errors"] ?? null)) {
            // line 69
            echo "      <div class=\"fieldset__error-message\">
        ";
            // line 70
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["errors"] ?? null), 70, $this->source), "html", null, true);
            echo "
      </div>
    ";
        }
        // line 73
        echo "    ";
        if (twig_get_attribute($this->env, $this->source, ($context["description"] ?? null), "content", [], "any", false, false, true, 73)) {
            // line 74
            echo "      <div";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["description"] ?? null), "attributes", [], "any", false, false, true, 74), "addClass", [0 => ($context["description_classes"] ?? null)], "method", false, false, true, 74), 74, $this->source), "html", null, true);
            echo ">";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["description"] ?? null), "content", [], "any", false, false, true, 74), 74, $this->source), "html", null, true);
            echo "</div>
    ";
        }
        // line 76
        echo "
    ";
        // line 77
        if (($context["inline_items"] ?? null)) {
            // line 78
            echo "      </div>
    ";
        }
        // line 80
        echo "  </div>
</fieldset>
";
    }

    public function getTemplateName()
    {
        return "core/themes/claro/templates/fieldset.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  172 => 80,  168 => 78,  166 => 77,  163 => 76,  155 => 74,  152 => 73,  146 => 70,  143 => 69,  140 => 68,  134 => 66,  132 => 65,  127 => 64,  121 => 62,  119 => 61,  116 => 60,  112 => 58,  110 => 57,  106 => 56,  103 => 55,  95 => 52,  90 => 51,  87 => 50,  83 => 48,  80 => 47,  78 => 43,  76 => 39,  75 => 38,  74 => 37,  73 => 35,  71 => 31,  70 => 30,  69 => 29,  68 => 27,  66 => 23,  65 => 21,  63 => 13,  62 => 11,);
    }

    public function getSourceContext()
    {
        return new Source("", "core/themes/claro/templates/fieldset.html.twig", "/var/www/html/web/core/themes/claro/templates/fieldset.html.twig");
    }
}
