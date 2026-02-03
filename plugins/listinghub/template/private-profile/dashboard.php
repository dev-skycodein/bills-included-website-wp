






 
    
    






 
    
    <style>
    .banner {
        display: flex;
        align-items:center;
        background:#a681f3;
        padding: 25px 25px 35px;
        border-radius: 25px;
    }
    .banner h2{
        color: #fff;
        margin: 0px 0 10px;
    }
    .banner p{
        color: #fff;
        line-height: 20px;
        margin: 0;
    }
    .banner .banner-column1{
        width: 70%;
    }
    .banner .banner-column2{
        width: 30%;
        text-align:center;
    }
    .banner .banner-column2 img{
        max-width: 170px;
    }
    .dashboard-2columns {
        display: flex;
        gap: 20px;
        margin: 20px 0;
    }

    .dashboard-column {
        background: #fff;
        padding: 20px 30px;
        border-radius: 25px;
        width: 100%;
    }

    .dashboard-column h2 {
        margin: 0 0 10px;
        font-size: 28px;
        font-weight: 400;
    }

    .dashboard-column a {
        background: #a681f3;
        color: #fff;
        padding: 10px 20px;
        border-radius: 25px;
        margin-top: 10px;
        display: inline-block;
    }
    .dashboard-recent-properties {
        background: #fff;
        padding: 40px 20px;
        text-align: center;
        border-radius: 25px;
    }
    .dashboard-recent-properties a {
        background: #a681f3;
        color: #fff;
        padding: 10px 20px;
        border-radius: 25px;
        margin-top: 10px;
        display: inline-block;
    }
    .dashboard-recent-properties h2{
        margin: 0 0 20px;
        font-size: 28px;
        font-weight: 400;
    }
    .dashboard-recent-properties p{
        max-width: 740px;
        margin: auto auto 10px;
    }
    @media(max-width: 991px) {
        .banner{
            flex-direction: column-reverse;
            justify-content: start;
        }
        .banner .banner-column1,.banner .banner-column2{
            width: 100%;
        }
        .banner .banner-column2{
            margin-bottom: 20px;
            text-align: left;
        }
        .listing-overview{
            padding: 20px 20px 30px 20px !important;
        }
        .dashboard-2columns {
            flex-direction: column;
        }
    }
    @media(max-width: 450px){
         .listing-overview{
            padding: 0 !important;
        }
    }
</style>
<div class="banner">
    <div class="banner-column1">
        <h2>Your Dashboard</h2>
        <p>
        The Bills Included is your all-in-one destination for effortless property experiences- whether you’re listing a home, looking to rent, or learning along the way.
        </p>
    </div>
    <div class="banner-column2">
        <img src="https://thebillsincluded.com/wp-content/uploads/2023/09/imageedit_14_2924889915-e1737313565296.png" class="banner-logo">
    </div>
</div>
<div class="dashboard-2columns">
    <div class="dashboard-column">
        <h2>Message</h2>
        <p>Head to your inbox now to view responses and keep up with the latest updates.</p>
        <a href="?profile=messageboard">Check inbox</a>
    </div>
    <?php if($renter){ ?>
    <div class="dashboard-column">
        <h2>How we can help you?</h2>
        <p>We are here to support you at every stage of your rental journey.</p>
        <a href="https://thebillsincluded.com/throughout-your-journey/">Read More</a>
    </div>
    <?php } else { ?>
    <div class="dashboard-column">
        <h2>Looking to rent your property?</h2>
        <p>List your home with ease - just follow our simple steps.</p>
        <a href="?profile=all-post">Create property listing</a>
    </div>
    <?php } ?>
</div>
<div class="dashboard-recent-properties">
    <h2>Recent Properties</h2>
    <p>Explore our latest all-inclusive rental listings - transparent pricing, no hidden fees. Whether you’re after a modern studio or a spacious family home, find your perfect fit today.</p>
    <a href="https://thebillsincluded.com/listing/">Recent Properties</a>
</div>