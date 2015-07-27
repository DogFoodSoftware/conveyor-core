Conveyor-Core
-------------

Bootstrap and core software components for Conveyor.

**These instructions aren't necessarily true yet.** :)

## Get the VM

Conveyor is designed to be used primarily from a VM and can be installed with:

```
curl -o Vagrantfile -fsSL https://raw.githubusercontent.com/DogFoodSoftware/conveyor-core/master/Vagrantfile
vagrant up
```

Then head to [http://192.168.33.10/] to view the dashboard.

## Install to Host

We recommend developers interact primarily with the VM. You can probably install the Conveyor system to any Ubuntu 'trusty' / 14.04 system. To do so:

```
su
bash -c "$(curl -fsSL https://raw.githubusercontent.com/DogFoodSoftware/conveyor-core/master/install)"
```

If you want to set up 

```
sudo conv-pkg install webserver conveyor-docs
```

And connect your browser to http://localhost.

Pre-Release-Notice
------------------

This project is pre-alpha.

