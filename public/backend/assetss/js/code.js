$(function(){
    $(document).on('click','#delete',function(e){
        e.preventDefault();
        var form = $(this).closest('form');
        var link = $(this).attr("href");

              Swal.fire({
                title: 'Are you sure?',
                text: "Delete This Data?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
              }).then((result) => {
                if (result.isConfirmed) {
                  if (form.length && form.is('form')) {
                    form.submit();
                  } else if (link) {
                    window.location.href = link;
                  }
                  Swal.fire(
                    'Deleted!',
                    'Your file has been deleted.',
                    'success'
                  )
                }
              })


    });

  });
