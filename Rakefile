require 'fileutils'
require 'rake'
require 'rake/packagetask'

PKG_VERSION = '0.9.1'

PROJECT_DIR = File.expand_path(File.dirname(__FILE__))
WORK_DIR    = File.join(PROJECT_DIR, 'work')
PKG_DIR     = File.join(PROJECT_DIR, 'pkg')

CSS_DIR     = File.join(PROJECT_DIR, 'www/assets/css')
JS_DIR      = File.join(PROJECT_DIR, 'www/assets/js')
JS_DEBUG_DIR = File.join(JS_DIR,     'debug')

BASE_DIR    = File.join(PROJECT_DIR, '../..')
EXT_DIR     = File.join(BASE_DIR,    'ext')
CLOSURE_DIR = File.join(EXT_DIR,     'closure-compiler')
CLOSURE_JAR = File.join(EXT_DIR,     'closure-compiler/compiler.jar')

task :default   => [:clean, 'css:build', 'js:build']
task :lint      => ['js:lint']

task :clean do
  rmtree WORK_DIR
end

directory WORK_DIR

#task :mk_work_dir => [:clean] do
#  Dir.mkdir WORK_DIR unless File.exist? WORK_DIR
#end

namespace :sync do
  task :js do
    cp get_ext_path('jquery/jquery.js'), JS_DEBUG_DIR
    cp get_ext_path('jquery.colorbox/jquery.colorbox.js'), JS_DEBUG_DIR
    cp get_ext_path('jquery.cookie/jquery.cookie.js'), JS_DEBUG_DIR
    cp get_ext_path('jquery.validate/jquery.validate.js'), JS_DEBUG_DIR
    cp get_ext_path('yepnope/yepnope.js'), JS_DEBUG_DIR

    cp get_js_debug_path('jquery.colorbox.js'), JS_DIR
    cp get_js_debug_path('jquery.validate.js'), JS_DIR

    compress_file get_js_path('jquery.colorbox.js')
    compress_file get_js_path('jquery.validate.js')
  end
end

namespace :css do
  task :build => [:build_site, :build_ie]

  task :build_site => WORK_DIR do
    puts 'Building site.css'
    yui_src = get_work_path '00-yui.css'
    src = get_work_path 'site.css'
    target = get_css_path 'site.css'

    concat_files Dir.glob(get_css_path 'yui/0*.css').sort, yui_src
    files = [yui_src] + Dir.glob(get_css_path '0*.css')
    concat_files files.sort, src

    yui_minify src, target
    compress_file target
  end

  task :build_ie do
    puts 'Building ie.css'
    src = get_css_path 'ie.debug.css'
    target = get_css_path 'ie.css'

    yui_minify src, target
    compress_file target
  end
end

namespace :js do
  task :build => [:build_main, :build_site]

  task :lint do
    [
      'debug/boot.js',
      'debug/narvalo.js',
      'debug/main.js',
      'debug/site.js'
    ].map { |f|
      get_js_path f
    }.each { |f|
      puts "Lint #{f}"
      `fixjsstyle --strict --nojsdoc #{f}`
    }
  end

  task :build_boot => WORK_DIR do
    puts 'Building boot.js'
    closure_compile get_js_path('debug/boot.js'), get_work_path('boot.js')
  end

  task :build_main => WORK_DIR do
    puts 'Building main.js'
    files = [
      'debug/yepnope.js',
      'debug/main.js'
    ].map { |f| get_js_path f }
    src = get_work_path 'main.js'
    target = get_js_path 'main.js'

    concat_files files, src
    closure_compile src, target
    compress_file target
  end

  task :build_site => WORK_DIR do
    puts 'Building site.js'
    files = [
      'debug/jquery.cookie.js',
      'debug/narvalo.js',
      'debug/site.js'
    ].map { |f| get_js_path f }
    src = get_work_path 'site.js'
    tmp = get_work_path 'site.min.js'
    target = get_js_path 'site.js'

    concat_files files, src
    closure_compile src, tmp
    concat_files [get_js_path('debug/jquery.js'), tmp], target
    compress_file target
  end
end

Rake::PackageTask.new('lobuki', PKG_VERSION) do |package|
  package.need_tar = true
  package.package_dir = PKG_DIR
  package.package_files.include(
    'lib/**/*',
    'views/**/*',
    'www/**/*'
  ).exclude(
    'www/assets/img/**/*'
  )
end

def get_css_path(file)
  File.join(CSS_DIR, file)
end

def get_ext_path(file)
  File.join(EXT_DIR, file)
end

def get_js_path(file)
  File.join(JS_DIR, file)
end

def get_js_debug_path(file)
  File.join(JS_DEBUG_DIR, file)
end

def get_php_path(file)
  File.join(PHP_DIR, file)
end

def get_work_path(file)
  File.join(WORK_DIR, file)
end

def concat_files(files, target)
  File.open(target, 'w') do |f|
    f.puts files.map { |file|
      File.read file
    }
  end
end

def closure_compile(src, target)
  `java -jar #{CLOSURE_JAR} --compilation_level SIMPLE_OPTIMIZATIONS --js #{src} --js_output_file #{target}`
end

def yui_minify(src, target)
  `yuicompressor #{src} -o #{target} --charset utf-8 --type css`
end

def compress_file(file)
  target = "#{file}.gz"
  `gzip -c -f -9 #{file} > #{target}`
end

