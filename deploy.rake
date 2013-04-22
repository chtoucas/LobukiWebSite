require 'rake'
require 'yaml'

PROJECT_DIR = File.expand_path(File.dirname(__FILE__))
CONFIG      = YAML.load_file('etc/deploy_test.yml')
RSYNC_CMD   = 'rsync -xrLptgoE --safe-links --delete-excluded --delete'

# Make the sources' paths absolute.
SRC = CONFIG['src'].inject({}) {
  |h, (k, v)| h[k] = File.join(PROJECT_DIR, v); h }
TARGET = CONFIG['target']

# Default task ----------------------------------------------------------------

task :default => [:deploy]

task :deploy => [:phplib, :statics, :views, :www]

# Core tasks ------------------------------------------------------------------

directory TARGET['phplib']

task :phplib => TARGET['phplib'] do
  puts 'Deploying PHP libraries...'
  rsync SRC['phplib'], TARGET['phplib']
end

directory TARGET['statics']

task :statics => TARGET['statics'] do
  puts 'Deploying static files...'
  rsync SRC['statics'], TARGET['statics']
end

directory TARGET['views']

task :views => TARGET['views'] do
  puts 'Deploying views...'
  rsync SRC['views'], TARGET['views']
end

directory TARGET['www']

task :www => TARGET['www'] do
  puts 'Deploying the web site...'
  rsync_with_excludes SRC['www'], TARGET['www'], ['assets']
end

# Helpers ---------------------------------------------------------------------

def rsync(src, dest)
  `#{RSYNC_CMD} #{src} #{dest}`
end

def rsync_with_excludes(src, dest, excludes)
  opt = excludes.map { |f| "--exclude '#{f}'" }.join(" ")
  `#{RSYNC_CMD} #{opt} #{src} #{dest}`
end

# EOF
