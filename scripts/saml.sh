if [ -L "vendor/simplesamlphp/simplesamlphp/config" ]; then
  echo "Removing existing simplesaml config symlink"
  rm -f vendor/simplesamlphp/simplesamlphp/config
  echo "Re-linking simplesaml config directory"
  ln -sf ../../../simplesamlphp/config vendor/simplesamlphp/simplesamlphp/config
fi
if [ ! -L "vendor/simplesamlphp/simplesamlphp/config" ]; then
  if [ -d "vendor/simplesamlphp/simplesamlphp/config" ]; then
    echo "Removing default simplesaml config directory"
    rm -rf vendor/simplesamlphp/simplesamlphp/config
  fi
  echo "Linking simplesaml config directory"
  ln -sf ../../../simplesamlphp/config vendor/simplesamlphp/simplesamlphp/config
fi

if [ -L "vendor/simplesamlphp/simplesamlphp/cert" ]; then
  echo "Removing existing simplesaml cert symlink"
  rm -f vendor/simplesamlphp/simplesamlphp/cert
  echo "Re-linking simplesaml cert directory"
  ln -s ../../../simplesamlphp/cert vendor/simplesamlphp/simplesamlphp/cert
fi
if [ ! -L "vendor/simplesamlphp/simplesamlphp/cert" ]; then
  if [ -d "vendor/simplesamlphp/simplesamlphp/cert" ]; then
    echo "Removing default simplesaml cert directory"
    rm -rf vendor/simplesamlphp/simplesamlphp/cert
  fi
  echo "Linking simplesaml cert directory"
  ln -s ../../../simplesamlphp/cert vendor/simplesamlphp/simplesamlphp/cert
fi

if [ -L "vendor/simplesamlphp/simplesamlphp/metadata" ]; then
  echo "Removing existing simplesaml metadata symlink"
  rm -f vendor/simplesamlphp/simplesamlphp/metadata
  echo "Re-linking simplesaml metadata directory"
  ln -s ../../../simplesamlphp/metadata vendor/simplesamlphp/simplesamlphp/metadata
fi
if [ ! -L "vendor/simplesamlphp/simplesamlphp/metadata" ]; then
  if [ -d "vendor/simplesamlphp/simplesamlphp/metadata" ]; then
    echo "Removing default simplesaml metadata directory"
    rm -rf vendor/simplesamlphp/simplesamlphp/metadata
  fi
  echo "Linking simplesaml metadata directory"
  ln -s ../../../simplesamlphp/metadata vendor/simplesamlphp/simplesamlphp/metadata
fi
