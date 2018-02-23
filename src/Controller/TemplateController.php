<?php
    namespace App\Controller;

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    use Leafo\ScssPhp\Compiler;

    class TemplateController extends Controller
    {
        /**
         * @Route("/{_locale}/admin/template", name="template")
         */
         final public function list(TranslatorInterface $translator, LogService $log)
         {
             return $this->render('setting/admin/list.html.twig', array(
                 'page_title' => $translator->trans('Settings'),
                 'can_add' => true,
                 'can_edit' => true,
                 'can_delete' => true,
             ));
         }

         /**
          * @Route("/{_locale}/admin/template/edit/{id}", name="template_edit")
          */
        public function edit()
        {
        }

         /**
          * @Route("/{_locale}/admin/template/compile", name="template_compile")
          */
        public function compile()
        {

            ini_set('max_execution_time', 300);
            // Compile frontend style
            try {
				$scss = new Compiler();
                $scss->setFormatter('Leafo\\ScssPhp\\Formatter\\Crunched');
                $scss->setImportPaths(array(
                    'assets/scss/',
                    'vendor/twbs/bootstrap/scss',
                ));
                $css = $scss->compile(file_get_contents('vendor/components/css-reset/reset.min.css'));
                $css .= $scss->compile('@import "style.scss";');
                $css .= $scss->compile(file_get_contents('vendor/daneden/animate.css/animate.min.css'));

                if (file_exists('public/css/style.css')) unlink('public/css/style.css');
				$myfile = fopen('public/css/style.css', 'w');
				fwrite($myfile, $css);
				fclose($myfile);
				chmod('public/css/style.css', 0644);

                $build_style = 1;

            } catch(Exception $e) {
            	$build_style = $e->getMessage();
            }

            // Compile admin style
            try {
				$scss = new Compiler();
                $scss->setFormatter('Leafo\\ScssPhp\\Formatter\\Crunched');
                $scss->setImportPaths(array(
                    'assets/scss/',
                    'vendor/twbs/bootstrap/scss',
                ));
                $css = $scss->compile(file_get_contents('vendor/components/css-reset/reset.min.css'));
                $css .= $scss->compile('@import "admin.scss";');
                $css .= $scss->compile(file_get_contents('vendor/daneden/animate.css/animate.min.css'));

                if (file_exists('public/css/admin.css')) unlink('public/css/admin.css');
				$myfile = fopen('public/css/admin.css', 'w');
				fwrite($myfile, $css);
				fclose($myfile);
				chmod('public/css/admin.css', 0644);

                $build_admin_style = 1;

            } catch(Exception $e) {
            	$build_admin_style = $e->getMessage();
            }

            // Create symlinks
            // https://symfony.com/doc/current/components/filesystem.html
            $symlinks = array(
                //'vendor/components/jquery/jquery.min.js' => 'public/vendor/jquery/jquery.min.js',
                //'vendor/components/jquery/jquery.min.map' => 'public/vendor/jquery/jquery.min.map',

                //'vendor/components/font-awesome/fonts/fontawesome-webfont.eot' => 'public/vendor/font-awesome/fonts/fontawesome-webfont.eot',
                //'vendor/components/font-awesome/fonts/fontawesome-webfont.svg' => 'public/vendor/font-awesome/fonts/fontawesome-webfont.svg',
                //'vendor/components/font-awesome/fonts/fontawesome-webfont.ttf' => 'public/vendor/font-awesome/fonts/fontawesome-webfont.ttf',
                //'vendor/components/font-awesome/fonts/fontawesome-webfont.woff' => 'public/vendor/font-awesome/fonts/fontawesome-webfont.woff',
                //'vendor/components/font-awesome/fonts/fontawesome-webfont.woff2' => 'public/vendor/font-awesome/fonts/fontawesome-webfont.woff2',
                //'vendor/components/font-awesome/fonts/FontAwesome.otf' => 'public/vendor/font-awesome/fonts/FontAwesome.otf',



            );

            if (!empty($symlinks))
            foreach($symlinks as $target => $link)
            {
                if (file_exists($link)) if (is_link($link)) unlink($link);
                if (!file_exists($link)) symlink($target, $link);
            }

            return $this->render('template/admin/compile.html.twig', array(
                'page_title' => 'Template compile',
            ));
        }
    }
