$script = <<SCRIPT

apt-get update
apt-get install sudo
gpasswd -a vagrant sudo

apt-get install libapache2-mod-php5 php5-curl -y
a2enmod rewrite
sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
apache2ctl restart

rm -Rf /var/www/html/
ln -s /vagrant/public/ /var/www/html

SCRIPT

Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.provision :shell, inline: $script
  config.vm.network :forwarded_port, guest: 80, host: 8000
end
