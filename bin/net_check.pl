#!/usr/bin/perl
use strict;
use Config::Tiny;
use Thread 'async';
use Getopt::Long;

my $remote_host;
my $count;
my $conf;
my $now = `/bin/date +%Y%m%d-%H%M%S`;
chomp $now;
open FH,">>/var/log/net_check.log";
Getopt::Long::GetOptions(
                'ip=s'  	=> \$remote_host,
		'conf=s'	=> \$conf,
		'count=s'	=> \$count
                );

if (!defined($conf)) {
	$conf = '/var/www/html/cloudOnCloud/bin/config.ini';
}
if (!defined($count)) {
	$count = 30;
}
if (!defined($remote_host)){
	print "$now: perl $0 -ip remote_host -conf config.ini -count 30\n";
	exit;
}
print FH "$now: perl $0 -ip $remote_host -conf $conf -count $count \n";
sub remote_ping {
	my ($source_host,$remote_host,$count) = @_;
	my @ping_info = `ssh -p 6022 root\@$source_host " ping -c $count $remote_host |tail -n 2 "`;
	my $packet_loss = (split /,/,$ping_info[0])[2];
	$packet_loss =~ s/^ //;
	my ($a,$ping_min,$ping_avg,$ping_max,$ping_mdev,$b,$value_min,$value_avg,$value_max,$value_mdev,$unit) = split / |\//,$ping_info[1];
	chomp ($a,$ping_min,$ping_avg,$ping_max,$ping_mdev,$b,$value_min,$value_avg,$value_max,$value_mdev,$unit);
#	print "from $source_host ping $remote_host $count packets \n";
#	print "loss:$packet_loss \n";
#	print "$ping_avg\t$value_avg $unit\n";
#	print "$ping_mdev\t$value_mdev $unit\n";
	return "\"loss\":\"$packet_loss\",\"$ping_min\":\"$value_min $unit\",\"$ping_max\":\"$value_max $unit\",\"$ping_avg\":\"$value_avg $unit\",\"$ping_mdev\":\"$value_mdev $unit\"";
}

my @ips;
my %t_ip;
my @region_type;
sub config {
	my $conf_file = shift;
#	my $Config = Config::Tiny->new;
        my $Config = Config::Tiny->read( "$conf_file" ) or print FH ("$now: Can not open config file:$conf_file\n");
        my $REGIONS = $Config->{BASE}->{REGION} or print FH ("$now: read config error:regions\n");
        my $TYPES = $Config->{BASE}->{TYPE} or print FH ("$now: read config error:type\n");
	my @region = split /,/,$REGIONS;
	my @type = split /,/,$TYPES;
	foreach my $zone ( @region ) {
		foreach my $net ( @type ) {
			my $name = $zone."-".$net;
			chomp $name;
			push (@region_type,$name);
		}
	}
	foreach my $title ( @region_type ) {
		my $ip = $Config->{$title}->{IP} or  print FH ("$now: WARNNING: read config error:$title IP\n");
#		print "$title $ip\n";
		if ( $ip ne '' ) {
			push (@ips,$ip);
			$t_ip{$title}=$ip;
		}
	}
}

&config($conf);
my %thread;
my %stuff;
foreach my $source_host (@ips) {
        $thread{$source_host}=async {
                eval{
			$stuff{$source_host}=&remote_ping($source_host,$remote_host,$count);
                };
        return $stuff{$source_host};
        }
}
foreach my $key (keys %thread) {
        $stuff{$key}=$thread{$key}->join();
}
my $JSON = "{\"count\":\"$count\",\"data\":[";
my @data ;
foreach my $line ( @region_type ) {
	if ( $t_ip{$line} ne '' ) {
		my $a = "{\"nettype\":\"$line\",\"ip\":\"$t_ip{$line}\",$stuff{$t_ip{$line}}}";
		push (@data,$a);
	}
}
my $data_str = join ",",@data ;
$JSON = $JSON.$data_str."]}";
print "$JSON\n";
print FH "$now: $JSON\n";
close FH;
exit 0;
